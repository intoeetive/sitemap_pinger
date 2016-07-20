<?php

/*
=====================================================
 Sitemap Pinger
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011 Yuriy Salimovskiy
=====================================================
 This software is based upon and derived from
 ExpressionEngine software protected under
 copyright dated 2004 - 2011. Please see
 http://expressionengine.com/docs/license.html
=====================================================
 File: ext.sitemap_pinger.php
-----------------------------------------------------
 Purpose: Ping search engines about changes in your sitemap
=====================================================
*/

if ( ! defined('BASEPATH'))
{
	exit('Invalid file request');
}

class Sitemap_pinger_ext {

	var $name	     	= 'Sitemap Pinger';
	var $version 		= '1.0.2';
	var $description	= 'Ping search engines about changes in your sitemap';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://www.intoeetive.com/docs/sitemap_pinger.html';
    
    var $settings 		= array();
    
    var $debug          = false;
    
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function __construct($settings = '')
	{
		$this->EE =& get_instance();
        $this->settings = $settings;
	}
    
    /**
     * Activate Extension
     */
    function activate_extension()
    {
        
        $hooks = array(
    		array(
    			'hook'		=> 'entry_submission_end',
    			'method'	=> 'ping',
    			'priority'	=> 10
    		)
    	);
    	
        foreach ($hooks AS $hook)
    	{
    		$data = array(
        		'class'		=> __CLASS__,
        		'method'	=> $hook['method'],
        		'hook'		=> $hook['hook'],
        		'settings'	=> '',
        		'priority'	=> $hook['priority'],
        		'version'	=> $this->version,
        		'enabled'	=> 'y'
        	);
            $this->EE->db->insert('extensions', $data);
    	}	
        
        $sql[] = "CREATE TABLE `".$this->EE->db->dbprefix."sitemap_pinger` (
                `ping_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `site_id` INT NOT NULL ,
                `sitemap_uri` VARCHAR( 255 ) NOT NULL ,
                `trigger_channels` TEXT NOT NULL ,
                `s_engines` TEXT NOT NULL ,
                INDEX ( `site_id` )
                )";
                
        $sql[] = "CREATE TABLE `".$this->EE->db->dbprefix."search_engines` (
                `se_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `se_name` VARCHAR( 255 ) NOT NULL ,
                `se_ping_url` VARCHAR( 255 ) NOT NULL
                )";
                
        $sql[] = "INSERT INTO `exp_search_engines` (
                    `se_id` ,
                    `se_name` ,
                    `se_ping_url`
                )
                VALUES (
                    NULL , 'Google', 'http://google.com/webmasters/sitemaps/ping?sitemap='
                ), (
                    NULL , 'Bing', 'http://www.bing.com/webmaster/ping.aspx?siteMap='
                ), (
                    NULL , 'Ask.com', 'http://submissions.ask.com/ping?sitemap='
                ), (
                    NULL , 'Yandex', 'http://webmaster.yandex.ru/wmconsole/sitemap_list.xml?host='
                )";
                
        foreach ($sql as $qstr)
        {
            $this->EE->db->query($qstr);
        }

    }
    
    /**
     * Update Extension
     */
    function update_extension($current = '')
    {
    	if ($current == '' OR $current == $this->version)
    	{
    		return FALSE;
    	}
    	
    	if ($current < '2.0')
    	{
    		// Update to version 1.0
    	}
    	
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update(
    				'extensions', 
    				array('version' => $this->version)
    	);
    }
    
    
    /**
     * Disable Extension
     */
    function disable_extension()
    {
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->delete('extensions');
        
        $sql[] = "DROP TABLE `".$this->EE->db->dbprefix."sitemap_pinger`"; 
        $sql[] = "DROP TABLE `".$this->EE->db->dbprefix."search_engines`";                
                    
        foreach ($sql as $qstr)
        {
            $this->EE->db->query($qstr);
        }
    }
        
    
    function settings_form($current)
    {
    	if ( ! class_exists('Sitemap_pinger_mcp'))
    	{
    		require_once PATH_THIRD.'sitemap_pinger/mcp.sitemap_pinger.php';
    	}
    	
    	$PINGER = new Sitemap_pinger_mcp();
        
        return $PINGER->sitemaps();
    }


    
    function ping($entry_id, $meta, $data) 
    {
        $data = array_merge($data, $meta);

    	//better workflow compatibility
		foreach($_POST as $k => $v) 
		{
			if (preg_match('/^epBwfEntry/',$k))
			{
				$data['status'] = array_pop(explode('|',$v));
				break;
			}
		}
		
		if ($data['status']!='open') return;
		
		//get all sitemaps for this site
        $this->EE->db->select('*');
        $this->EE->db->from('sitemap_pinger');
        $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
        $sitemaps = $this->EE->db->get();
        if ($sitemaps->num_rows()==0)
        {
            return false;
        }
        
        //get all search engines
        $this->EE->db->select('*');
        $this->EE->db->from('search_engines');
        $engines = $this->EE->db->get();
        if ($engines->num_rows()==0)
        {
            return false;
        }
        $all_engines = array();
        $engine_names = array();
        foreach ($engines->result_array() as $engine)
        {
            $all_engines[$engine['se_id']] = $engine['se_ping_url'];
            $engine_names[$engine['se_id']] = $engine['se_name'];
        }
        
        $use_curl = function_exists('curl_init');
        
        if ($this->debug==true)
        {
            $this->EE->load->library('logger');
        }
        
        foreach ($sitemaps->result_array() as $sitemap)
        {
            $this_channels = unserialize($sitemap['trigger_channels']);
            if (in_array($meta['channel_id'], $this_channels))
            {
                $this_engines = unserialize($sitemap['s_engines']);
                foreach ($this_engines as $engine)
                {
                    if (array_key_exists($engine, $all_engines))
                    {
                        $ping_url = $all_engines[$engine].$sitemap['sitemap_uri'];
                        if ($use_curl === true)
                        {
                            $ping = $this->_ping_curl($ping_url);
                        }
                        else
                        {
                            $ping = $this->_ping_socket($ping_url);
                        }
                        if ($this->debug==true)
                        {
                            if ($ping == true)
                            {
                                $this->EE->logger->log_action("Sending to ".$engine_names[$engine]." of sitemap ".$sitemap['sitemap_uri']." - success");
                            }
                            else
                            {
                                $this->EE->logger->log_action("Sending to ".$engine_names[$engine]." of sitemap ".$sitemap['sitemap_uri']." - FAILURE");
                            }
                        }
                        
                    }
                }
            }
        }

        return false;
        
    }
    
    
   	function _ping_curl($url) 
	{	
		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_HEADER, TRUE);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($handle);
		curl_close($handle);
		
		if (!preg_match("/^HTTP\/[0-9\.]+ 200 /", $response))
		{
			return false; 
		}
		
		return true;
	}
 
	function _ping_socket($url) 
	{	
		$url = parse_url($url);
		
		if (!isset($url["port"])) 
		{
			$url["port"] = 80;
		}
		
		if (!isset($url["path"])) 
		{
			$url["path"] = "/";
		}

		$fp = @fsockopen($url["host"], $url["port"], $errno, $errstr, 30);

		if ($fp) 
		{
			$http_request = "HEAD ".$url["path"]."?".$url["query"]." HTTP/1.1\r\n"."Host: ".$url["host"]."\r\n"."Connection: close\r\n\r\n";
			fputs($fp, $http_request);
      		$response = fgets($fp, 1024);
			fclose($fp);
						
			if (strpos($response, 'HTTP/1.1 200 OK') === 0)
			{
				return true; 
			}
		}
		
		return false;
	}

}
// END CLASS
