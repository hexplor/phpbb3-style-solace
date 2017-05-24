<?php
/**
*
* @package phpBB Extension - Devlom Configurator
* @copyright (c) 2015 Devlom
*
*/

namespace devlom\configurator\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Parser;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
  static public function getSubscribedEvents()
  {
    return array(
      'core.user_setup'						=> 'load_language_on_setup',
      'core.page_header'						=> 'header_actions',
      'core.adm_page_header' => 'admin_header',
      /* Forumlist last avatar */
      'core.display_forums_modify_sql'			=> 'add_user_sql',
      'core.display_forums_modify_forum_rows'		=> 'forums_modify_forum_rows',
      'core.display_forums_modify_template_vars'	=> 'forums_last_post_avatar',
      /* Viewforum last avatar */
      'core.viewforum_modify_topics_data'			=> 'add_user_viewforum_sql',
      'core.viewforum_modify_topicrow'			=> 'viewforum_last_post_avatar',
      /* Search last avatar */
      'core.search_get_topic_data'				=> 'add_user_search_sql',
      'core.search_modify_tpl_ary'				=> 'search_last_post_avatar',
      /* Connect Recent topics By PayBas */
      'paybas.recenttopics.sql_pull_topics_data'	=> 'add_user_rct_sql',
      'paybas.recenttopics.modify_tpl_ary'		=> 'rct_last_post_avatar',
    );
  }

  /* @var \phpbb\controller\helper */
  protected $helper;

  /* @var \phpbb\template\template */
  protected $template;

  /** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

  const MAX_SIZE = 128; // Max size img

  /**
  * Constructor
  *
  * @param \phpbb\controller\helper	$helper		Controller helper object
  * @param \phpbb\template			$template	Template object
  */
  public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\path_helper $path_helper, \phpbb\db\driver\driver_interface $db)
  {
    $this->user = $user;
		$this->path_helper = $path_helper;
		$this->db = $db;
    $this->helper = $helper;
    $this->template = $template;
    global $yaml;
    $yaml = new Parser();
  }

  public function load_language_on_setup($event)
  {
    $lang_set_ext = $event['lang_set_ext'];
    $lang_set_ext[] = array(
      'ext_name' => 'devlom/configurator',
      'lang_set' => 'common',
    );
    $event['lang_set_ext'] = $lang_set_ext;


  }

  public function admin_header($event)
  {
    global $config, $request, $user, $template, $phpbb_root_path, $cache, $yaml;

    // AdminFields
    $admin_fields = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/admin/fields.yaml";
    if(file_exists($admin_fields)) {
      if ($cache->_exists('_yaml_admfields') === false || filemtime($admin_fields) > $cache->get('_yaml_admfields_time')) {
        $yaml_array = $yaml->parse(file_get_contents($admin_fields));
        $cache->put('_yaml_admfields', $yaml_array);
        $cache->put('_yaml_admfields_time', filemtime($admin_fields));
        $template->assign_vars(array(
          'admin_fields' =>  $yaml_array,
        )
      );
    } else {
      $template->assign_vars(array(
        'admin_fields' =>  $cache->get('_yaml_admfields'),
        )
      );
    }
  }
  // Config
  $yaml_config = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/config.yaml";
  if(file_exists($yaml_config)) {
    if ($cache->_exists('_yaml_config') === false || filemtime($yaml_config) > $cache->get('_yaml_config_time')) {
      $yaml_array = $yaml->parse(file_get_contents($yaml_config));
      $cache->put('_yaml_config', $yaml_array);
      $cache->put('_yaml_config_time', filemtime($yaml_config));
      $template->assign_vars(array(
        'saved_config' =>  $yaml_array,
      )
    );
  } else {
    $template->assign_vars(array(
      'saved_config' =>  $cache->get('_yaml_config'),
      )
    );
  }
}
// Presets
$yaml_presets = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/presets.yaml";

if(file_exists($yaml_presets)) {
  if ($cache->_exists('_yaml_presets') === false || filemtime($yaml_presets) > $cache->get('_yaml_presets_time')) {
    $yaml_array = $yaml->parse(file_get_contents($yaml_presets));
    $cache->put('_yaml_presets', $yaml_array);
    $cache->put('_yaml_presets_time', filemtime($yaml_presets));
    $template->assign_vars(
    array(
      'presets' =>  $yaml_array,
    )
  );
} else {
  $template->assign_vars(array(
    'presets' =>  $cache->get('_yaml_presets'),
    )
  );
}
}

}

public function header_actions($event)
{
  global $config, $request, $user, $template, $phpbb_root_path, $cache, $yaml;

  // StyleSwitcher
  $HostDomain = str_replace("http://","",$request->server('HTTP_HOST'));
  $expire = time()+60*60*24*30; // how long to remember preset choice (60*60*24*30 = 30 days)
  $preset = $request->variable('preset', '');

  if (!empty($preset)) {
    $_SESSION['preset'] = $preset;
    $user->set_cookie('preset', $preset, $expire, "/", $HostDomain, 0);
    header("Location: ". $request->server('PHP_SELF'));
  }

  $cookie = $request->variable($config['cookie_name'] . '_preset', '', true, \phpbb\request\request_interface::COOKIE);

  if (!empty($cookie)) {
    $savedcss = $cookie;
  } else {
    $savedcss = '';
  }

  $template->assign_vars(array(
    'switcher_preset_name'        => $cookie,
  ));

  // Presets
  $yaml_presets = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/presets.yaml";

  if(file_exists($yaml_presets)) {
    if ($cache->_exists('_yaml_presets') === false || filemtime($yaml_presets) > $cache->get('_yaml_presets_time')) {
      $yaml_array = $yaml->parse(file_get_contents($yaml_presets));
      $cache->put('_yaml_presets', $yaml_array);
      $cache->put('_yaml_presets_time', filemtime($yaml_presets));
      $template->assign_vars(
      array(
        'presets' =>  $yaml_array,
      )
    );
  } else {
    $template->assign_vars(array(
      'presets' =>  $cache->get('_yaml_presets'),
      )
    );
  }
}

// Load content
function load_content($filename) {
  global $user, $phpbb_root_path, $cache, $yaml, $template;
  $content = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/content/". $filename . ".yaml";
  if(file_exists($content)) {
    if ($cache->_exists('_yaml_'.$filename) === false || filemtime($content) > $cache->get('_yaml_' .$filename. '_time')) {
      $yaml_array = $yaml->parse(file_get_contents($content));
      $cache->put('_yaml'.$filename, $yaml_array);
      $cache->put('_yaml_' .$filename. '_time', filemtime($content));
      $template->assign_vars(array(
        $filename =>  $yaml_array,
      )
    );
  } else {
    $template->assign_vars(array(
      $filename =>  $cache->get('_yaml_'.$filename),
      )
    );
  }
}
}

// Content elements
$content_elements = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/content.yaml";
if(file_exists($content_elements)) {
  if ($cache->_exists('_yaml_elements') === false || filemtime($content_elements) > $cache->get('_yaml_elements_time')) {
    $yaml_array = $yaml->parse(file_get_contents($content_elements));
    $cache->put('_yaml_elements', $yaml_array);
    $cache->put('_yaml_elements_time', filemtime($content_elements));
    $elements = $yaml_array;
  } else {
    $elements = $cache->get('_yaml_elements');
  }
  if (!empty($elements)) {
    foreach ($elements as $key => $element) {
      load_content($element['filename']);
    }
  }
}

// Saved Config
$yaml_config = $phpbb_root_path."styles".DIRECTORY_SEPARATOR.$user->style['style_path']."/config.yaml";
if(file_exists($yaml_config)) {
  if ($cache->_exists('_yaml_config') === false || filemtime($yaml_config) > $cache->get('_yaml_config_time')) {
    $yaml_array = $yaml->parse(file_get_contents($yaml_config));
    $cache->put('_yaml_config', $yaml_array);
    $cache->put('_yaml_config_time', filemtime($yaml_config));
    $template->assign_vars(array(
      'saved_config' =>  $yaml_array,
    )
  );
} else {
  $template->assign_vars(array(
    'saved_config' =>  $cache->get('_yaml_config'),
    )
  );
}
}

}
/** Data request forum */
public function add_user_sql($event)
{
  $sql_ary = $event['sql_ary'];
  $sql_ary['LEFT_JOIN'][] = array(
    'FROM' => array(USERS_TABLE => 'u'),
    'ON' => "u.user_id = f.forum_last_poster_id AND forum_type != " . FORUM_CAT
  );
  $sql_ary['SELECT'] .= ', u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height';
  $event['sql_ary'] = $sql_ary;
}

public function forums_modify_forum_rows($event)
{
  $forum_rows = $event['forum_rows'];
  $parent_id = $event['parent_id'];
  $row = $event['row'];

  if (isset($forum_rows[$parent_id]['user_last_post_time']) && $row['forum_last_post_time'] > $forum_rows[$parent_id]['user_last_post_time'])
  {
    $forum_rows[$parent_id]['user_last_post_time'] = $row['forum_last_post_time'];
    $forum_rows[$parent_id]['user_avatar'] = $row['user_avatar'];
    $forum_rows[$parent_id]['user_avatar_type'] = $row['user_avatar_type'];
    $forum_rows[$parent_id]['user_avatar_width'] = $row['user_avatar_width'];
    $forum_rows[$parent_id]['user_avatar_height'] = $row['user_avatar_height'];
    $event['forum_rows'] = $forum_rows;
  }
}
/* User avatar Last post in forum */
public function forums_last_post_avatar($event)
{
  $row = $event['row'];
  $row = $this->avatar_img_resize($row);
  $forum_row['AVATAR_IMG'] = phpbb_get_user_avatar($row);
  $event['forum_row'] += $forum_row;
}

/** Data request viewforum */
public function add_user_viewforum_sql($event)
{
  $rowset = $event['rowset'];
  $topic_last_poster_id = array();
  foreach ($event['topic_list'] as $topic_id)
  {
    $row = &$rowset[$topic_id];
    $topic_last_poster_id[] = $row['topic_last_poster_id'];
    unset($rowset[$topic_id]);
  }

  if (sizeof($topic_last_poster_id))
  {
    $sql = 'SELECT user_id, user_avatar, user_avatar_type, user_avatar_width, user_avatar_height
      FROM ' . USERS_TABLE . '
      WHERE ' . $this->db->sql_in_set('user_id', $topic_last_poster_id);
    $result = $this->db->sql_query($sql);
    while($userrow = $this->db->sql_fetchrow($result))
    {
      $this->userrow[$userrow['user_id']] = $userrow;
    }
    $this->db->sql_freeresult($result);
  }
}

/* User avatar Last post in viewforum */
public function viewforum_last_post_avatar($event)
{
  $row = $event['row'];
  if (!empty($this->userrow[$row['topic_last_poster_id']]))
  {
    $topic_row = $event['topic_row'];
    $userrow = $this->avatar_img_resize($this->userrow[$row['topic_last_poster_id']]);
    $topic_row['LAST_POST_AUTHOR_FULL'] .= '<span class="lastpostavatar">' . phpbb_get_user_avatar($userrow) . '</span>';
    $event['topic_row'] = $topic_row;
  }
}

/** Data request reccent topics */
public function add_user_search_sql($event)
{
  $event['sql_from'] .= ' LEFT JOIN ' . USERS_TABLE . ' us ON (us.user_id = t.topic_last_poster_id)';
  $event['sql_select'] .= ', us.user_avatar, us.user_avatar_type, us.user_avatar_width, us.user_avatar_height';
}

/* User avatar Last post in recent topics */
public function search_last_post_avatar($event)
{
  $row = $event['row'];
  $tpl_ary = $event['tpl_ary'];
  if (isset($tpl_ary['LAST_POST_AUTHOR_FULL']))
  {
    $row = $this->avatar_img_resize($row);
    $tpl_ary['LAST_POST_AUTHOR_FULL'] .= '<span class="lastpostavatar">' . phpbb_get_user_avatar($row) . '</span>';
    $event['tpl_ary'] = $tpl_ary;
  }
}

/** Data request reccent topics */
public function add_user_rct_sql($event)
{
  $sql_array = $event['sql_array'];
  $sql_array['LEFT_JOIN'][] = array(
    'FROM' => array(USERS_TABLE => 'u'),
    'ON' => "u.user_id = f.forum_last_poster_id"
  );
  $sql_array['SELECT'] .= ', u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height';
  $event['sql_array'] = $sql_array;
}

/* User avatar Last post in recent topics */
public function rct_last_post_avatar($event)
{
  $row = $event['row'];
  $tpl_ary = $event['tpl_ary'];
  $row = $this->avatar_img_resize($row);
  $tpl_ary['LAST_POST_AUTHOR_FULL'] .= '<span class="lastpostavatar">' . phpbb_get_user_avatar($row) . '</span>';
  $event['tpl_ary'] = $tpl_ary;
}

/* Generate and resize avatar */
private function avatar_img_resize($avatar)
{
  if (!empty($avatar['user_avatar']))
  {
    if ($avatar['user_avatar_width'] >= $avatar['user_avatar_height'])
    {
      $avatar_width = ($avatar['user_avatar_width'] > self::MAX_SIZE) ? self::MAX_SIZE : $avatar['user_avatar_width'];
      if ($avatar_width == self::MAX_SIZE)
      {
        $avatar['user_avatar_height'] = round(self::MAX_SIZE/$avatar['user_avatar_width']*$avatar['user_avatar_height']);
      }
      $avatar['user_avatar_width'] = $avatar_width;
    }
    else
    {
      $avatar_height = ($avatar['user_avatar_height'] > self::MAX_SIZE) ? self::MAX_SIZE : $avatar['user_avatar_height'];
      if ($avatar_height == self::MAX_SIZE)
      {
        $avatar['user_avatar_width'] = round(self::MAX_SIZE/$avatar['user_avatar_height']*$avatar['user_avatar_width']);
      }
      $avatar['user_avatar_height'] = $avatar_height;
    }
  }
  else
  {
    $no_avatar = "{$this->path_helper->get_web_root_path()}styles/" . rawurlencode($this->user->style['style_path']) . '/theme/images/no_avatar.gif';
    $avatar = array(
      'user_avatar' => $no_avatar,
      'user_avatar_type' => AVATAR_REMOTE,
      'user_avatar_width' => self::MAX_SIZE,
      'user_avatar_height' => self::MAX_SIZE,
    );
  }
  return $avatar;
}

}
