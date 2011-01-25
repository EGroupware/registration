<?php
/**
 * Registration - General storage object
 *
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @package registration
 * @copyright (c) 2011 by Nathan Gray
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * General storage object for Registration
 *
 * Registrant information is actually stored in the addressbook.  Registration only
 * needs to (permanently) store IP and timestamp, and what they registered for.
 */
class registration_so extends so_sql {

	public function __construct() {
		parent::__construct(
			'registration',
			'egw_registration'
		);
	}
}
