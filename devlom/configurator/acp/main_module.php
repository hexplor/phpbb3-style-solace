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
    if(file_exists($admin_fields)) {
      if ($cache->_exists('_yaml_admfields') === false || filemtime($admin_fields) > $cache->get('_yaml_admfields_time')) {
        $yaml_array = $yaml->parse(file_get_contents($admin_fields));
        $cache->put('_yaml_admfields', $yaml_array);
        $cache->put('_yaml_admfields_time', filemtime($admin_fields));
        $fields = $yaml_array;
      } else {
        $fields = $cache->get('_yaml_admfields');
      }
    }

    // Load saved config
    $config_file = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/config.yaml";
    $saved_config = array();
    if(file_exists($config_file)) {
      $saved_config = $yaml->parse(file_get_contents($config_file));
    }

    // Load presets
    $presets_file = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/presets.yaml";
    $presets = array();
    if(file_exists($presets_file)) {
      $presets = $yaml->parse(file_get_contents($presets_file));
    }

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('devlom/configurator'))
			{
				trigger_error('FORM_INVALID');
			}

      $array = '';
      foreach ($fields as $value) {
        $array[] = array(str_replace(' ', '_', strtolower($value['name'])) => $request->variable(str_replace(' ', '_', strtolower($value['name'])), array('' => '' ), true) );
      }
      $array = call_user_func_array('array_merge', $array);

			$dumper = new Dumper();
			$yaml = $dumper->dump($array, 3);
			file_put_contents($phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/config.yaml", $yaml);

			trigger_error($user->lang('ACP_CONFIGURATOR_SETTING_SAVED'));
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
