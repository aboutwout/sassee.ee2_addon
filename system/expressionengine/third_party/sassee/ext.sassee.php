<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sassee Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Wouter Vervloet
 * @link		http://www.baseworks.nl/
 */

require PATH_THIRD.'sassee/config.php';

class Sassee_ext {
	
	public $settings = array();
	public $description = SASSEE_DESCRIPTION;
	public $docs_url = SASSEE_DOCUMENTATION;
	public $name = SASSEE_NAME;
	public $settings_exist = 'y';
	public $version = SASSEE_VERSION;
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->settings = $this->get_settings();
		
	}
	
	function __get($key='')
	{
	  if ( ! $key OR ! array_key_exists($key, $this->settings)) return NULL;
	  
	  $val = $this->settings[$key];
	  
	  if (in_array($key, array('css_path', 'css_url', 'sass_path')))
	  {
	    $val = $this->EE->functions->remove_double_slashes($val . '/');
	  }
	  
	  return $val;
	  
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 *
	 * If you wish for ExpressionEngine to automatically create your settings
	 * page, work in this method.  If you wish to have fine-grained control
	 * over your form, use the settings_form() and save_settings() methods 
	 * instead, and delete this one.
	 *
	 * @see http://expressionengine.com/user_guide/development/extensions.html#settings
	 */
	public function settings()
	{

		return array(
			'css_path' => @$this->settings['css_path'],
			'css_url' => @$this->settings['css_url'],
			'sass_path' => @$this->settings['sass_path'],
		  'style' => array('s', array('nested', 'expanded', 'compact', 'compressed'), @$this->settings['style']),
		  'syntax' => array('s', array('sass', 'scss'), @$this->settings['syntax'])
		);
	}
	
	function save_settings()
	{
	  
	  $settings = array();
	  $settings[$this->EE->config->item('site_id')] = array(
			'css_path' => $this->EE->input->post('css_path'),
			'css_url' => $this->EE->input->post('css_url'),
			'sass_path' => $this->EE->input->post('sass_path'),
		  'style' => $this->EE->input->post('style'),
		  'syntax' => $this->EE->input->post('syntax')
	  );
	  
	  $this->EE->db->where('class', __CLASS__);
  	$this->EE->db->update('extensions', array('settings' => serialize($settings)));
	  
	}
	
	function dummy_method()
	{
	  return;
	}
	
	function get_settings()
	{
	  
		$query = $this->EE->db->get_where('extensions', array('class' => __CLASS__));
		
		if ($query->num_rows() > 0)
		{
		  $settings = unserialize($query->row('settings'));
		}
		
//		debug($settings);
		return $settings;
//		return isset($settings[$this->EE->config->item('site_id')]) ? $settings[$this->EE->config->item('site_id')] : array();
			  
	}
	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$default_settings = array(
		  'sass_path' => @$_SERVER['DOCUMENT_ROOT'].'/sass/',
		  'css_path' => @$_SERVER['DOCUMENT_ROOT'].'/css/',
		  'css_url' => '/css',
		  'style' => 'nested',
		  'syntax' => 'sass'
		);
		
		foreach ($this->EE->db->get('sites')->result() as $row)
		{
		  $this->settings[$row->site_id] = $default_settings;
		}
		
    // hooks array
    $hooks = array('dummy_hook' => 'dummy_method');

    // insert hooks and methods
    foreach ($hooks AS $hook => $method)
    {
      // data to insert
      $data = array(
        'class'		=> __CLASS__,
        'method'	=> $method,
        'hook'		=> $hook,
        'priority'	=> 1,
        'version'	=> $this->version,
        'enabled'	=> 'y',
        'settings'	=> serialize($this->settings)
      );

      // insert in database
      $this->EE->db->insert('exp_extensions', $data);
    }
    
    return TRUE;
		
		
		// No hooks selected, add in your own hooks installation code here.
	}	

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.sassee.php */
/* Location: /system/expressionengine/third_party/sassee/ext.sassee.php */