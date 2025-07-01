<?php
/**
*
* @package phpBB Extension - Devlom Configurator
* @copyright (c) 2015 Devlom
*
*/

namespace devlom\configurator\acp;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $request;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

    $yaml = new Parser();
		$user->add_lang('acp/common');
		$this->tpl_name = 'configurator_body';
		$this->page_title = $user->lang('ACP_CONFIGURATOR_TITLE');
		add_form_key('devlom/configurator');

    $admin_fields = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/admin/fields.yaml";
    $fields = array();
    
    if(file_exists($admin_fields)) {
      // Always load fresh data to avoid cache issues during debugging
      $yaml_array = $yaml->parse(file_get_contents($admin_fields));
      if (is_array($yaml_array)) {
        $fields = $yaml_array;
      } else {
        trigger_error('Failed to parse admin fields YAML file', E_USER_ERROR);
      }
    } else {
      trigger_error('Admin fields file not found: ' . $admin_fields, E_USER_ERROR);
    }
    
    if (empty($fields)) {
      trigger_error('Failed to load admin fields configuration', E_USER_ERROR);
    }

    // Load saved config
    $config_file = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/config.yaml";
    $saved_config = array();
    if(file_exists($config_file)) {
      $config_data = $yaml->parse(file_get_contents($config_file));
      if (is_array($config_data)) {
        $saved_config = $config_data;
      }
    }

    // Load presets
    $presets_file = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/presets.yaml";
    $presets = array();
    if(file_exists($presets_file)) {
      $presets_data = $yaml->parse(file_get_contents($presets_file));
      if (is_array($presets_data)) {
        $presets = $presets_data;
      }
    }

		if ($request->is_set_post('submit'))
		{
			// Debug: Log that we're processing a POST request
			error_log('Configurator: Processing POST submission');
			
			// Check form token
			if (!check_form_key('devlom/configurator'))
			{
				error_log('Configurator: Form token invalid - stopping execution');
				trigger_error('FORM_INVALID');
			}
			
			error_log('Configurator: Form token is valid, continuing...');
			
			try {
			
			error_log('Configurator: Starting data collection...');
			
			// Debug: Show what data was received
			$debug_data = array();
			if (isset($_POST) && is_array($_POST)) {
				error_log('Configurator: POST is array with ' . count($_POST) . ' items');
				foreach($_POST as $key => $value) {
				  if ($key != 'form_token' && $key != 'submit') {
				    $debug_data[$key] = $value;
				    error_log('Configurator: Added key: ' . $key);
				  }
				}
			} else {
				error_log('Configurator: POST is not an array!');
			}
			error_log('Configurator: POST data collected, count: ' . count($debug_data));

      $array = array();
      $config_file = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/config.yaml";
      
      // Check if directory exists and is writable
      $config_dir = dirname($config_file);
      if (!is_dir($config_dir)) {
        trigger_error('Configuration directory does not exist: ' . $config_dir, E_USER_ERROR);
      }
      if (!is_writable($config_dir)) {
        trigger_error('Configuration directory is not writable: ' . $config_dir . '. Please check file permissions.', E_USER_ERROR);
      }
      
      // Build configuration array from form data
      foreach ($fields as $section) {
        $section_name = str_replace(' ', '_', strtolower($section['name']));
        error_log('Configurator: Processing section: ' . $section_name);
        
        // Get array data using phpBB's proper array syntax
        $section_data = $request->variable($section_name, array('' => ''), true);
        error_log('Configurator: Section data for ' . $section_name . ': ' . print_r($section_data, true));
        
        if (!empty($section_data)) {
          $array[$section_name] = $section_data;
          error_log('Configurator: Added section ' . $section_name . ' to config array');
        }
      }
      
      error_log('Configurator: Final config array: ' . print_r($array, true));
      
      if (empty($array)) {
        $debug_msg = 'No configuration data received from form. POST data: ' . print_r($debug_data, true) . 
                     ' Fields count: ' . count($fields);
        trigger_error($debug_msg, E_USER_ERROR);
      }

			$dumper = new Dumper();
			$yaml_content = $dumper->dump($array, 3);
			
			// Try to write the file
			$write_result = file_put_contents($config_file, $yaml_content);
			
			if ($write_result === false) {
			  trigger_error('Failed to write configuration file: ' . $config_file . '. Please check file permissions.', E_USER_ERROR);
			}
			
			// Clear cache after successful save
			$cache->destroy('_yaml_config');
			$cache->destroy('_yaml_config_time');

			error_log('Configurator: Save completed successfully');
			
			// Check if this is an AJAX request
			if ($request->is_ajax()) {
				$json_response = new \phpbb\json_response();
				$json_response->send(array(
					'MESSAGE_TITLE' => $user->lang('INFORMATION'),
					'MESSAGE_TEXT' => $user->lang('ACP_CONFIGURATOR_SETTING_SAVED'),
					'REFRESH_DATA' => array(
						'time' => 3,
						'url' => $this->u_action
					)
				));
			} else {
				trigger_error($user->lang('ACP_CONFIGURATOR_SETTING_SAVED'));
			}
			
			} catch (Exception $e) {
				error_log('Configurator: Exception caught: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
				trigger_error('Configuration save failed: ' . $e->getMessage(), E_USER_ERROR);
			} catch (Error $e) {
				error_log('Configurator: Fatal error caught: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
				trigger_error('Configuration save failed: ' . $e->getMessage(), E_USER_ERROR);
			}
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'DEVLOM_CONFIGURATOR_GOODBYE'		=> $config['devlom_configurator_goodbye'],
      'ACTUAL_STYLE' => $user->style['style_path'],
      'admin_fields' => $fields,
      'saved_config' => $saved_config,
      'presets' => $presets,
		));
	}
}
