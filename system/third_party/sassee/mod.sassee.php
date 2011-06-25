<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Sassee Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Wouter Vervloet
 * @link		http://www.baseworks.nl/
 */

class Sassee {
	
	public $return_data;
	
	
	function Sassee()
	{
	  $this->__construct();
	}
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('sass');		
	}
	
	function link()
	{
		$this->file = $this->_fetch_param('file');
		$this->template = $this->_fetch_param('template');
		
		if ($this->file !== FALSE)
		{
		  $this->return_data =  $this->_parse_file($this->file, TRUE);
		}
		elseif ($this->template !== FALSE)
		{
		  $this->return_data = $this->_parse_template($this->template);
    }
    
    // TODO
//    $this->return_data = 'filename.css';
    
    return $this->return_data;
	  
	}
	
	function parse()
	{
		$source = $this->EE->TMPL->tagdata;

    $this->return_data = $this->EE->sass->parse($source, FALSE);
	  
    return $this->return_data;
	  
	}
	
	function _parse_file($filename='')
	{
	  return $this->EE->sass->parse($filename, TRUE);	  
	}

	function _parse_template($template='')
	{
	  if ( ! $template) return '';
	  
	  $source = $this->_fetch_template($template);
	  
	  return $this->EE->sass->parse($source, FALSE);
	}
	
	function _fetch_template($template='')
	{
	  $template_data = '';
	  
	  $parts = explode('/', $template);
	  
	  $template_group = @$parts[0];
	  $template_name = isset($parts[1]) ? $parts[1] : 'index';
	  
	  if ( ! $template_group OR ! $template_name) return '';
	  
	  $query = $this->EE->db->select('templates.template_data')->from('templates')->join('template_groups', 'templates.group_id=template_groups.group_id')->where(array('templates.template_name' => $template_name, 'template_groups.group_name' => $template_group))->get();
	  
	  if ( $query->num_rows() > 0)
	  {
	    $template_data = $query->row('template_data'); 
	  }
	  return $template_data;
	}
	
  /**
  * Helper function for getting a parameter
  */		 
  function _fetch_param($key='', $default_value = FALSE)
  {
    $val = $this->EE->TMPL->fetch_param($key);

    if ($val === '' OR $val === FALSE)
    {
      return $default_value;
    }
    
    return $val;
  }	
  
  function _fetch_bool_param($key='', $default_value = FALSE)
  {
    $val = $this->_fetch_param($key, $default_value);
    
    return in_array($val, array('y', 'yes', '1', 'true')) ? TRUE : FALSE;
  }	
	
}
/* End of file mod.sassee.php */
/* Location: /system/expressionengine/third_party/sassee/mod.sassee.php */