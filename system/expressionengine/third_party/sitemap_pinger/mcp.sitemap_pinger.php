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
 File: mcp.sitemap_pinger.php
-----------------------------------------------------
 Purpose: Ping search engines about changes in your sitemap
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}



class Sitemap_pinger_mcp {

    var $version = '1.0.2';
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
    } 
    
    function index()
    {
        return $this->sitemaps();
    }
    
    function engines()
    {
        $this->EE->load->helper('form');
    	$this->EE->load->library('table');   
        $this->EE->load->library('javascript');

    	$vars = array();
 
        $this->EE->db->select('*');
        $this->EE->db->from('search_engines');
        $query = $this->EE->db->get();
        
        $i = 1;
        $vars['table_headings'] = array(
                        '#',
                        $this->EE->lang->line('search_engine'),
                        '',
                        ''
                    );
                    
        $vars['data'] = array();      
        foreach ($query->result() as $obj)
        {
           $vars['data'][$i]['id'] = $i;
           $vars['data'][$i]['se_name'] = $obj->se_name;
           $vars['data'][$i]['edit_link'] = "<a href=\"".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=edit_engine'.AMP.'id='.$obj->se_id."\">".$this->EE->lang->line('edit')."</a>";
           $vars['data'][$i]['delete_link'] = "<a class=\"engine_delete_warning\" href=\"".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=delete_engine'.AMP.'id='.$obj->se_id."\">".$this->EE->lang->line('delete')."</a>";
           
           $i++;
        }
        
        $outputjs = '
				var draft_target = "";

			$("<div id=\"engine_delete_warning\">'.$this->EE->lang->line('delete_warning').'</div>").dialog({
				autoOpen: false,
				resizable: false,
				title: "'.$this->EE->lang->line('confirm_deleting').'",
				modal: true,
				position: "center",
				minHeight: "0px", 
				buttons: {
					Cancel: function() {
					$(this).dialog("close");
					},
				"'.$this->EE->lang->line('delete').'": function() {
					location=draft_target;
				}
				}});

			$(".engine_delete_warning").click( function (){
				$("#engine_delete_warning").dialog("open");
				draft_target = $(this).attr("href");
				$(".ui-dialog-buttonpane button:eq(2)").focus();	
				return false;
		});';

		$this->EE->javascript->output(str_replace(array("\n", "\t"), '', $outputjs));
        
        $this->EE->cp->set_variable('cp_page_title', lang('sitemap_pinger_module_name'));
        
        $this->EE->cp->set_right_nav(
            array( 'add_engine' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=edit_engine') 
        );
        
    	return $this->EE->load->view('engines', $vars, TRUE);
	
    }    
    



    function sitemaps()
    {
        $this->EE->load->helper('form');
    	$this->EE->load->library('table');   
        $this->EE->load->library('javascript');

    	$vars = array();
 
        $this->EE->db->select('*');
        $this->EE->db->from('sitemap_pinger');
        $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
        $query = $this->EE->db->get();
        
        $i = 1;
        $vars['table_headings'] = array(
                        '#',
                        $this->EE->lang->line('sitemap'),
                        '',
                        ''
                    );
                    
        $vars['data'] = array();           
        foreach ($query->result() as $obj)
        {
           $vars['data'][$i]['id'] = $i;
           $vars['data'][$i]['url'] = $obj->sitemap_uri;
           $vars['data'][$i]['edit_link'] = "<a href=\"".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=edit_sitemap'.AMP.'id='.$obj->ping_id."\">".$this->EE->lang->line('edit')."</a>";
           $vars['data'][$i]['delete_link'] = "<a class=\"engine_delete_warning\" href=\"".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=delete_sitemap'.AMP.'id='.$obj->ping_id."\">".$this->EE->lang->line('delete')."</a>";
           
           $i++;
        }
        
        $outputjs = '
				var draft_target = "";

			$("<div id=\"engine_delete_warning\">'.$this->EE->lang->line('delete_warning').'</div>").dialog({
				autoOpen: false,
				resizable: false,
				title: "'.$this->EE->lang->line('confirm_deleting').'",
				modal: true,
				position: "center",
				minHeight: "0px", 
				buttons: {
					Cancel: function() {
					$(this).dialog("close");
					},
				"'.$this->EE->lang->line('delete').'": function() {
					location=draft_target;
				}
				}});

			$(".sitemap_delete_warning").click( function (){
				$("#sitemap_delete_warning").dialog("open");
				draft_target = $(this).attr("href");
				$(".ui-dialog-buttonpane button:eq(2)").focus();	
				return false;
		});';

		$this->EE->javascript->output(str_replace(array("\n", "\t"), '', $outputjs));
        
        $this->EE->cp->set_variable('cp_page_title', lang('sitemap_pinger_module_name'));
        
        $this->EE->cp->set_right_nav(
            array( 'add_sitemap' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=edit_sitemap') 
        );
        
    	return $this->EE->load->view('sitemaps', $vars, TRUE);
	
    }    
    
    
    
    function delete_sitemap()
    {

        if (!empty($_GET['id']))
        {
            $this->EE->db->where('ping_id', $this->EE->input->get_post('id'));
            $this->EE->db->delete('sitemap_pinger');
            if ($this->EE->db->affected_rows()>0)
            {
                $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('deleted')); 
            }
            else
            {
                $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('nothing_to_delete'));  
            }
            
        }
        else 
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('nothing_to_delete'));  
        }

        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=sitemaps');
 
    }    
    


    function delete_engine()
    {

        if (!empty($_GET['id']))
        {
            $this->EE->db->where('se_id', $this->EE->input->get_post('id'));
            $this->EE->db->delete('search_engines');
            if ($this->EE->db->affected_rows()>0)
            {
                $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('deleted')); 
            }
            else
            {
                $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('nothing_to_delete'));  
            }
            
        }
        else 
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('nothing_to_delete'));  
        }

        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=engines');
 
    }    
    
    
    
    function edit_engine()
    {
    	$this->EE->load->helper('form');
    	$this->EE->load->library('table');  
        $this->EE->load->library('javascript');
        
    	$vars = array();
 
        $id = intval($this->EE->input->get('id'));
        if ($id!=0)
        {
            $this->EE->db->select('*');
            $this->EE->db->from('search_engines');
            $this->EE->db->where('se_id', $id);
            $query = $this->EE->db->get();

            $vars['data'] = array(	
                ''	=> form_hidden('id', $query->row('se_id')),
                'name'	=> form_input('se_name', $query->row('se_name'), 'style="width: 80%"'),
                'se_ping_url'	=> form_input('se_ping_url', $query->row('se_ping_url'), 'style="width: 80%"')
        		);
        }
        else
        {
            $vars['data'] = array(	
                ''	=> form_hidden('id', ''),
                'name'	=> form_input('se_name', '', 'style="width: 80%"'),
                'se_ping_url'	=> form_input('se_ping_url', '', 'style="width: 80%"')
        		);
        }
        
        $this->EE->cp->set_variable('cp_page_title', lang('sitemap_pinger_module_name'));
        
    	return $this->EE->load->view('edit_engine', $vars, TRUE);
	
    }    
    
    
    function save_engine()
    {

        if (empty($_POST['se_name']) || empty($_POST['se_ping_url']))
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('need_all_data'));
            $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=engines');
            return false;
        }

        $data['se_name'] = $this->EE->input->get_post('se_name');
        $data['se_ping_url'] = $this->EE->input->get_post('se_ping_url');
       
        
        if (!empty($_POST['id']))
        {
            $this->EE->db->where('se_id', $this->EE->input->post('id'));
            $this->EE->db->update('search_engines', $data);
        }
        else
        {
            $this->EE->db->insert('search_engines', $data);
        }
        
        $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('updated'));        
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=engines');
        
        
    }    


    function edit_sitemap()
    {
    	$this->EE->load->helper('form');
    	$this->EE->load->library('table');  
        $this->EE->load->library('javascript');
        
    	$vars = array();
        
        $this->EE->db->select('*');
        $this->EE->db->from('search_engines');
        $engines = $this->EE->db->get();
        
        $this->EE->db->select('channel_id, channel_title');
        $this->EE->db->from('channels');
        $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
        $channels = $this->EE->db->get();

        $id = intval($this->EE->input->get('id'));
        if ($id!=0)
        {
            $this->EE->db->select('*');
            $this->EE->db->from('sitemap_pinger');
            $this->EE->db->where('ping_id', $id);
            $query = $this->EE->db->get();
            
            $this_engines = unserialize($query->row('s_engines'));
            $this_channels = unserialize($query->row('trigger_channels'));

            $vars['data'] = array(	
                ''	=> form_hidden('id', $query->row('ping_id')),
                'sitemap_uri'	=> form_input('sitemap_uri', $query->row('sitemap_uri'), 'style="width: 80%"'),
                'engines_to_ping'	=> '',
                'trigger_channels'	=> ''
        		);
            foreach ($engines->result() as $engine)
            {
                $vars['data']['engines_to_ping'] .= form_checkbox(array('name'=>'s_engines[]', 'id'=>'s_engines_'.$engine->se_id, 'value'=>$engine->se_id, 'checked'=>in_array($engine->se_id, $this_engines)))." ".form_label($engine->se_name, 's_engines_'.$engine->se_id)." ";
            }
            foreach ($channels->result() as $channel)
            {
                $vars['data']['trigger_channels'] .= form_checkbox(array('name'=>'trigger_channels[]', 'id'=>'trigger_channels_'.$channel->channel_id, 'value'=>$channel->channel_id, 'checked'=>in_array($channel->channel_id, $this_channels)))." ".form_label($channel->channel_title, 'trigger_channels_'.$channel->channel_id)." ";
            }
        }
        else
        {
            $vars['data'] = array(	
                ''	=> form_hidden('id', ''),
                'sitemap_uri'	=> form_input('sitemap_uri', '', 'style="width: 80%"'),
                'engines_to_ping'	=> '',
                'trigger_channels'	=> ''
        		);
            foreach ($engines->result() as $engine)
            {
                $vars['data']['engines_to_ping'] .= form_checkbox(array('name'=>'s_engines[]', 'id'=>'s_engines_'.$engine->se_id, 'value'=>$engine->se_id, 'checked'=>true))." ".form_label($engine->se_name, 's_engines_'.$engine->se_id)." ";
            }
            foreach ($channels->result() as $channel)
            {
                $vars['data']['trigger_channels'] .= form_checkbox(array('name'=>'trigger_channels[]', 'id'=>'trigger_channels_'.$channel->channel_id, 'value'=>$channel->channel_id, 'checked'=>true))." ".form_label($channel->channel_title, 'trigger_channels_'.$channel->channel_id)." ";
            }
        }
        
        $this->EE->cp->set_variable('cp_page_title', lang('sitemap_pinger_module_name'));
        
    	return $this->EE->load->view('edit_sitemap', $vars, TRUE);
	
    }    
    
    
    function save_sitemap()
    {

        if (empty($_POST['sitemap_uri']))
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('need_sitemap'));
            $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=sitemaps');
            return false;
        }

        $data['sitemap_uri'] = $this->EE->input->get_post('sitemap_uri');
        $data['site_id'] = $this->EE->config->item('site_id');
        $data['trigger_channels'] = serialize($this->EE->security->xss_clean($_POST['trigger_channels']));
        $data['s_engines'] = serialize($this->EE->security->xss_clean($_POST['s_engines']));
       
        
        if (!empty($_POST['id']))
        {
            $this->EE->db->where('ping_id', $this->EE->input->post('id'));
            $this->EE->db->update('sitemap_pinger', $data);
        }
        else
        {
            $this->EE->db->insert('sitemap_pinger', $data);
        }
        
        $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('updated'));        
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap_pinger'.AMP.'method=sitemaps');
        
    }    
    

}
/* END */
?>