<?php
/**
*
* @package phpBB Extension - Devlom Configurator
* @copyright (c) 2015 Devlom
*
*/

namespace devlom\configurator\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['devlom_configurator_goodbye']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\alpha2');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('devlom_configurator_goodbye', 0)),

			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_CONFIGURATOR_TITLE'
			)),
			array('module.add', array(
				'acp',
				'ACP_CONFIGURATOR_TITLE',
				array(
					'module_basename'	=> '\devlom\configurator\acp\main_module',
					'modes'				=> array('settings'),
				),
			)),
		);
	}
}
