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
		
		// define a constant for the current site_id rather than calling $PREFS->ini() all the time
		if (defined('SITE_ID') === FALSE)
			define('SITE_ID', $this->EE->config->item('site_id'));

		if (class_exists('Sassee_ext') === FALSE)
			include(PATH_THIRD. 'sassee/ext.sassee.php');

		$SEX = new Sassee_ext;
		$this->settings = $SEX->get_settings();
			  
		$this->_file = $this->_fetch_param('file');
		$this->_template = $this->_fetch_param('template');

    // Fetch source from a file
    if ($this->_file)
    {
      $this->_source = $this->_get_source_from_file($this->_file);
    }
    // Fetch source from a template
    elseif ($this->_template)
    {
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
	  	  	  
	  $css_file = str_replace('.sass', '.css', $this->_file);	  	
	  	  	
    $output = $this->_parse($this->_source['contents']);
    
    if ( ! file_put_contents($this->settings['css_path'].$css_file, $output))
    {
      $this->_log("Could not write file '$css_file'");
      return FALSE;
    }

    $css_url = $this->EE->functions->remove_double_slashes($this->settings['css_url'].'/').$css_file;
    
    if ( ! file_exists($this->settings['css_path'].$css_file))
    {
      $this->_log("Could find file '$css_url'");
      return FALSE;
    }
    
    $this->return_data = $css_url;
    
    return $this->return_data;
	  
	}
	// END file
	
	function _parse($source='')
	{
	  
    $this->EE->load->library('sass');	  
	  
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
    
	  $source = file_get_contents($this->settings['sass_path'].$file);
	  $date_modified = filemtime($this->settings['sass_path'].$file);
    
    return array(
      'contents' => $source,
      'date_modified' => $date_modified
    );
	  
	}
	
	function _get_source_from_template($template='')
	{
	  if ( ! $template) return '';
	}
	
	function _fetch_file()
	{
	   
	}
	
	
	
	
	
	
	
	function _fetch_template($template='')
	{
	  $template_data = '';
	  
    // filemtime() // get the date the file was last modified
	  
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
	// END _parse_template
	
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
  
  function _log($message='')
  {
    if ( ! $message) return;
    
    $this->EE->TMPL->log_item('Module: '.SASSEE_NAME.' => '.$message);
    
  }
	
}
/* End of file mod.sassee.php */
/* Location: /system/expressionengine/third_party/sassee/mod.sassee.php */