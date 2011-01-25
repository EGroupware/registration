<?php
/**
 * Registration - admin, config and other hooks
 *
 * @link http://www.egroupware.org
 * @package registration
 * @author Nathan Gray
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Class containing administration, configuration and other hooks
 */
class registration_hooks {

	/**
	 * hooks to build sidebox-menu plus the admin and preferences sections
	 *
	 * @param string/array $args hook args
	 */
	static function all_hooks($args) {
		$appname = 'registration';
		$location = is_array($args) ? $args['location'] : $args;

		if ($GLOBALS['egw_info']['user']['apps']['admin'] && $location != 'preferences')
		{

			$title = $appname;
			$file = Array(
				'Site Configuration'	=> $GLOBALS['egw']->link('/index.php', 'menuaction=registration.registration_ui.config'),
			);

			if ($location == 'admin')
			{
				display_section($appname,$file);
			}
			else
			{
				display_sidebox($appname,lang('Admin'),$file);
			}
		}
	}


	/**
	 * Hook called by link-class to include registration in the appregistry of the linkage
	 *
	 * @param array/string $location location and other parameters (not used)
	 * @return array with method-names
	 */
	static function search_link($location) {
		return array(
			'query' => 'registration.registration_bo.link_query',
			'title' => 'registration.registration_bo.link_title',
		//	'titles' => 'registration.registration_bo.link_titles',
			'view' => array(
				'menuaction' => 'registration.registration_ui.view'
			),
			'view_id' => 'reg_id',
			'view_list'     =>      'registration.registration_ui.index',
		//	'add' => array(
		//		'menuaction' => 'registration.registration_ui.edit'
		//	),
		);
	}
}
?>
