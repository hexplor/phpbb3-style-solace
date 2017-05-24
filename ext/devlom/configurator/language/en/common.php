<?php
/**
*
* @package phpBB Extension - Devlom Configurator
* @copyright (c) 2015 Devlom
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'CONFIGURATOR_PAGE'			=> 'Configurator',
	'CONFIGURATOR_HELLO'		=> 'Hello %s!',
	'CONFIGURATOR_GOODBYE'		=> 'Goodbye %s!',

	'ACP_CONFIGURATOR_TITLE'			=> 'Devlom Configurator',
	'ACP_CONFIGURATOR'					=> 'Settings',
	'ACP_CONFIGURATOR_GOODBYE'			=> 'Should say goodbye?',
	'ACP_CONFIGURATOR_SETTING_SAVED'	=> 'Settings have been saved successfully!',
));
