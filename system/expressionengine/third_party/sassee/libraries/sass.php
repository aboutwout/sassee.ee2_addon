<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sass library
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Wouter Vervloet
 * @link		http://www.baseworks.nl/
 */

if ( ! class_exists('SassParser')) 
{
  require 'sass/SassParser.php';  
}

class Sass {

  var $settings = array(
    'cache' => FALSE,
    'cache_location' => '',
    'css_location' => '',
    'debug_info' => FALSE,
    'filename' => '',
    'line' => 0,
    'line_numbers' => 0,
    'load_paths' => array(),
    'property_syntax' => 'new',
    'quiet' => FALSE,
    'style' => 'nested', // nested|expanded|compact|compressed
    'syntax' => 'sass',
    'template_location' => '',
    'vendor_properties' => ''
  );
  
  function Sass($settings=array())
  {
    $this->__construct($settings);
  }
  
  function __construct($config=array())
  {
    if (is_array($config))
    {
      $this->settings = array_merge($this->settings, $config);      
    }
    
    $this->sass = new SassParser($this->settings);
  }
  
  function parse($source='', $is_file=FALSE)
  {    
    return $this->sass->toCss($source, $is_file);
  }

}