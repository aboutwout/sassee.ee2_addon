<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sassee Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Wouter Vervloet
 * @link		http://www.baseworks.nl/
 */

require PATH_THIRD.'sassee/config.php';

class Sassee {
	
  /**
  * ...
  * @access		public
  * @var			string
  **/	
	public $return_data;
	
  /**
  * Defines the default settings for an initial installation of this addon.
  * @access		public
  * @var			array an array of keys and values
  **/
	public $settings = array();
	
	
  /**
  * ...
  * @access		private
  * @var			string
  **/
	private $_file = '';

  /**
  * ...
  * @access		private
  * @var			string
  **/	
	private $_template = '';

  /**
  * ...
  * @access		private
  * @var			string
  **/	
	private $_source = '';

  /**
  * ...
  * @access		private
  * @var			boolean
  **/	
	private $_parse_source = TRUE;
	
	
	
	
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
		
		// Load the Sassee helper
		$this->EE->load->helper('sassee');
		
		// define a constant for the current site_id rather than calling $PREFS->ini() all the time
		if (defined('SITE_ID') === FALSE)
			define('SITE_ID', $this->EE->config->item('site_id'));

		if (class_exists('Sassee_ext') === FALSE)
			include(PATH_THIRD. 'sassee/ext.sassee.php');

		$SEX = new Sassee_ext;
		$this->settings = $SEX->get_settings();
			  
    if ($this->settings)
    {
      $this->settings['css_path'] = @$this->EE->functions->remove_double_slashes($this->settings['css_path'].'/');
      $this->settings['css_url'] = @$this->EE->functions->remove_double_slashes($this->settings['css_url'].'/');
      $this->settings['sass_path'] = @$this->EE->functions->remove_double_slashes($this->settings['sass_path'].'/');
    }
			  
		$this->_file = $this->_fetch_param('file');
		$this->_template = $this->_fetch_param('template');

    // Fetch source from a file
    if ($this->_file)
    {
      $this->_output_file = css_filename($this->_file);
      $this->_source = $this->_get_source_from_file($this->_file);
    }
    // Fetch source from a template
    elseif ($this->_template)
    {      
      $this->_output_file = css_filename($this->_template);

      $this->_source = $this->_get_source_from_template($this->_template);      

    }
    
	}
	// END __construct
	
  function output()
  {
    // If source is empty, abort here
      if ( ! $this->_source) return '';
      
      $output = $this->_parse($this->_source['contents']);
   
  }
	// END output
	
	function file()
	{

	  // If source is empty, abort here
    if ( ! $this->_source) return '';
	  
	  if ($this->_parse_source === FALSE)
	  {
      $this->return_data = $this->_source;
	  }	
	  else
	  {
      $output = $this->_parse($this->_source);

      $this->return_data = $this->write_css_file($this->_output_file, $output);	    
	  }
    
    return $this->return_data;
	  
	}
	// END file
	
	function _parse($source='')
	{

	  // If source is empty, abort here
	  if ( ! $source) return '';
	  
	  // Load SASS library
    $this->EE->load->library('sass');
    
    // Return parse SASS source as CSS
	  return $this->EE->sass->parse($source, FALSE);

	}
	// END _parse_source
	
	function _get_source_from_file($file='')
	{
	  if ( ! $file)
	  {
      $this->_log("Source file was not specified");
      return FALSE;
	  }
	  
    if ( ! file_exists($this->settings['sass_path'].$file))
    {
      $this->_log("Source file \'$file\' does not exist");
      return FALSE;
    }
    
	  $source_date_modified = filemtime($this->settings['sass_path'].$file);
    
    $css_filename = css_filename($file);
    
    // If a css file exists and it is as old as the source file
    if (file_exists($this->settings['css_path'].$css_filename) AND filemtime($this->settings['css_path'].$css_filename) > $source_date_modified)
    {
      $this->_parse_source = FALSE;
      return $this->settings['css_url'].$css_filename;
    }

	  return file_get_contents($this->settings['sass_path'].$file);
	  
	}
	
	function _get_source_from_template($template='')
	{
	  if ( ! $template) return '';
	  
		// Parse HTML EE Template
	  $parts = explode('/', $template);	  
	  $template_group = @$parts[0];
	  $template_name = isset($parts[1]) ? $parts[1] : 'index';
	  
	  if ($template_group  AND $template_name)
	  {
	    
	    // Hijack the template engine
  		$TMPL = new Inline_template();
  		$TMPL->run_template_engine($template_group, $template_name);
  		$source = $TMPL->final_template;
  		$source_date_modified = $TMPL->template_edit_date;
  		
	    $css_filename = $template_name.'.css';
      
	    if (file_exists($this->settings['css_path'].$css_filename) AND filemtime($this->settings['css_path'].$css_filename) > $source_date_modified)
      {
        $this->_log("Loading '$css_filename'");
      }
	    
  	  return $source;
	  }
	  
	  return FALSE;
	  
	}
	
	function write_css_file($file='', $contents='')
	{
	  $filename = css_filename($file);	  		  	  	
    
    if ( ! file_exists($this->settings['css_path']))
    {
      mkdir($this->settings['css_path']);
    }
    
    if ( ! file_put_contents($this->settings['css_path'].$filename, $contents))
    {
      $this->_log("Could not write file '$filename'");
      return FALSE;
    }

    return $this->settings['css_url'].$filename;

	  
	}	
	
	/*****************************
	* Helper functions
	*****************************/
	
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
    
    return in_array($val, array('y', 'yes', '1', 'true', 'on', TRUE));
  }	
  
  function _log($message='')
  {
    if ( ! $message) return;
    
    $this->EE->TMPL->log_item('Module: '.SASSEE_NAME.' => '.$message);
    
  }
	
}

if ( ! class_exists('Inline_template'))
{

  /**
  * Overloaded EE Template class for Internal Template parsing
  *
  * @package Sassee
  */
  class Inline_template extends EE_Template
  {
  	function Inline_template()
  	{
  		parent::__construct();
  	}

  	function fetch_template($template_group, $template, $show_default = TRUE, $site_id = '')
  	{
  		if ($template_group == '!inline')
  		{
  			return $template;
  		}
  		else    
  		{
  			return parent::fetch_template($template_group, $template, $show_default, $site_id);
  		}
  	}
  }
  // END Inline_template class
}
/* End of file mod.sassee.php */
/* Location: /system/expressionengine/third_party/sassee/mod.sassee.php */