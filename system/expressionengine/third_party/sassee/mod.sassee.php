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
  * @var			string
  **/	
  private $_css_url = '';

  /**
  * ...
  * @access		private
  * @var			string
  **/	
  private $_css_path = '';

  /**
  * ...
  * @access		private
  * @var			string
  **/	
  private $_sass_path = '';

  /**
  * ...
  * @access		private
  * @var			boolean
  **/	
  private $_parse_source = TRUE;

  /**
  * ...
  * @access		private
  * @var			string
  **/	
  private $_output_filename = '';
	
  /**
  * PHP 4 Constructor
  */
  public function Sassee()
  {
    $this->__construct();
  }
	
  /**
  * PHP 5 Constructor
  */
  public function __construct()
  {
    $this->EE =& get_instance();

    // Load the Sassee helper
    $this->EE->load->helper('sassee');

    // define a constant for the current site_id rather than calling $PREFS->ini() all the time
    if ( ! defined('SITE_ID'))
    {
      define('SITE_ID', $this->EE->config->item('site_id'));
    }

    if ( ! class_exists('Sassee_ext'))
    {
      include(PATH_THIRD. 'sassee/ext.sassee.php');
    }

    $SEX = new Sassee_ext;

    $this->_css_path = $SEX->css_path;
    $this->_css_url = $SEX->css_url;
    $this->_sass_path = $SEX->sass_path;

    $this->_file = $this->_fetch_param('file');
    $this->_template = $this->_fetch_param('template');

    $this->_style = $this->_fetch_param('style', $SEX->style);
    $this->_syntax = $this->_fetch_param('syntax');
    $this->_output_filename = $this->_fetch_param('output_file');

    $this->_debug = $this->_fetch_bool_param('debug', $SEX->debug);

    if ($this->_debug === TRUE)
    {
      $this->_style = 'expanded';
    }

    // Fetch source from a file
    if ($this->_file)
    { 
      
      if ($this->_syntax === FALSE)
      {
        $syntax = get_sass_syntax($this->_file);        
        $this->_syntax = $syntax ? $syntax : $SEX->syntax;
      }
      
      if ( ! $this->_output_filename)
      {
        $this->_output_filename = css_filename($this->_file);        
      }
            
      $this->_source = $this->_get_source_from_file($this->_file);
    }
    // Fetch source from a template
    elseif ($this->_template)
    {      
      $this->_syntax = $this->_syntax ? $this->_syntax : $SEX->syntax;
      $this->_output_filename = css_filename($this->_template);
      $this->_source = $this->_get_source_from_template($this->_template);      
    }

  }
  // END __construct
	
  ######################################
  #  Module tags
  ######################################
	
  /**
  * ...
  * @access		public
  * @return   string
  **/	
  public function file()
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
      $this->return_data = $this->_write_css_file($output);	    
    }

    return $this->return_data;

  }
  // END file
	
	
  ######################################
  #  Private functions
  ######################################
	
  /**
  * ...
  * @access		private
  * @return   mixed array|string
  **/		
  private function _parse($source='')
  {
    // If source is empty, abort here
    if ( ! $source) return '';

    // Load SASS library
    $this->EE->load->library('sass', array(
      'style' => $this->_style,
      'syntax' => $this->_syntax,
      'debug_info' => $this->_debug,
      'filename' => isset($this->_file) ? $this->_file : $this->_template
    ));
    
    // Return parse SASS source as CSS
    return $this->EE->sass->parse($source, FALSE);
  }
  // END _parse
	
  /**
  * ...
  * @access		private
  * @return   mixed boolean|string
  **/	
  private function _get_source_from_file($file='')
  {
    if ( ! $file)
    {
      $this->_log("Source file was not specified");
      return FALSE;
    }

    if ( ! file_exists($this->_sass_path.$file))
    {
      $this->_log("Source file '{$file}' does not exist");
      return FALSE;
    }

    $source_date_modified = filemtime($this->_sass_path.$file);

    // If a css file exists and it is as old as the source file
    if ( ! $this->_debug AND file_exists($this->_css_path.$this->_output_filename) AND filemtime($this->_css_path.$this->_output_filename) > $source_date_modified)
    {
      $this->_log("Loading previously parsed CSS file '{$this->_output_filename}'");
      $this->_parse_source = FALSE;
      return $this->_css_url.$this->_output_filename;
    }

    return file_get_contents($this->_sass_path.$file);
  }
  // END _get_source_from_file
	
	
  /**
  * ...
  * @access		private
  * @return   mixed boolean|string
  **/	
  private function _get_source_from_template($template='')
  {
    if ( ! $template) return FALSE;

    // Parse HTML EE Template
    $parts = explode('/', $template);	  
    $template_group = @$parts[0];
    $template_name = isset($parts[1]) ? $parts[1] : 'index';

    if ($template_group  AND $template_name)
    {

      // Hijack the template engine
      $TMPL = new Sassee_template();
      $TMPL->run_template_engine($template_group, $template_name);
      $source = $TMPL->final_template;
      $source_date_modified = $TMPL->template_edit_date;

      $css_filename = $template_name.'.css';

      if (file_exists($this->_css_path.$css_filename) AND filemtime($this->_css_path.$css_filename) > $source_date_modified)
      {
        $this->_log("Loading previously parsed CSS file '{$this->_output_filename}'");
        $this->_parse_source = FALSE;
        return $this->_css_url.$this->_output_filename;
      }

      return $source;
    }

    return FALSE;

  }
  // END _get_source_from_template

  /**
  * ...
  * @access		private
  * @return   mixed boolean|string
  **/	
  private function _write_css_file($contents='')
  {

    if ( ! file_exists($this->_css_path))
    {
      mkdir($this->_css_path);
    }

    if ( ! file_put_contents($this->_css_path.$this->_output_filename, $contents))
    {
      $this->_log("Could not write file '{$this->_output_filename}'");
      return FALSE;
    }

    return $this->_css_url.$this->_output_filename;
  }	
  // END _write_css_file
	
	
  ######################################
  #  Helper functions
  ######################################
	
  /**
  * Helper function for getting a parameter
  * @access		private
  * @param    $key string 
  * @return   string
  **/	
  private function _fetch_param($key='', $default_value = FALSE)
  {
    $val = $this->EE->TMPL->fetch_param($key);

    if ($val === '' OR $val === FALSE)
    {
      return $default_value;
    }

    return $val;
  }	

  /**
  * Helper function for getting a parameter that
  * should return a boolean value.
  * @access		private
  * @param    $key string 
  * @return   string
  **/	 
  private function _fetch_bool_param($key='', $default_value = FALSE)
  {
    $val = $this->_fetch_param($key, $default_value);

    return in_array($val, array('y', 'yes', '1', 'true', 'on', TRUE));
  }	

  /**
  * Log message to the template log
  * @access		private
  * @param    $message string
  * @return   void
  **/  
  function _log($message='')
  {
    if ( ! $message) return;

    $this->EE->TMPL->log_item('--> '.SASSEE_NAME.' : '.$message);

  }
	
}

if ( ! class_exists('Sassee_template'))
{

  /**
  * Overloaded EE Template class for Internal Template parsing
  *
  * @package Sassee
  */
  class Sassee_template extends EE_Template
  {
    function Sassee_template()
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
  // END Sassee_template class
}

/* End of file mod.sassee.php */
/* Location: /system/expressionengine/third_party/sassee/mod.sassee.php */