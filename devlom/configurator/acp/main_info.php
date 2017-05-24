<?php
/**
*
* @package phpBB Extension - Devlom Configurator
* @copyright (c) 2015 Devlom
*
*/

namespace devlom\configurator\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\devlom\configurator\acp\main_module',
			'title'		=> 'ACP_CONFIGURATOR_TITLE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'settings'	=> array('title' => 'ACP_CONFIGURATOR', 'auth' => 'ext_devlom/configurator && acl_a_board', 'cat' => array('ACP_CONFIGURATOR_TITLE')),
			),
		);
	}
}
