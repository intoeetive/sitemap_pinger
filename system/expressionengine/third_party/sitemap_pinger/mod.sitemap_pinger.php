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
 File: mod.sitemap_pinger.php
-----------------------------------------------------
 Purpose: Ping search engines about changes in your sitemap
=====================================================
*/


if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}


class Sitemap_pinger {

    var $return_data	= ''; 						// Bah!

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
    	$this->EE =& get_instance(); 
    }
    /* END */


}
/* END */
?>