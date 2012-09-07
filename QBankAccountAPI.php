<?php
require_once 'QBankAPI.php';

require_once 'model/Group.php';

/**
 * Provides functionality for accounts (users, groups, etc.) in QBank.
 * @author Björn Hjortsten
 * @copyright Kaigan 2012
 * @package QBankAPIWrapper
 */
class QBankAccountAPI extends QBankAPI {
	
	/**
	 * Gets all the Groups the currently logged in user is member of.
	 * @author Björn Hjortsten
	 * @return Group[]
	 */
	public function getGroups() {
		$result = $this->call('getgroups', array());
		$groups = array();
		foreach ($result->groups as $group) {
			$groups[] = Group::createFromRawObject($group);
		}
		return $groups;
	}
	
	/**
	 * Gets a specific Group.
	 * @param int $id The id of the Group to get.
	 * @author Björn Hjortsten
	 * @return Group
	 */
	public function getGroup($id) {
		$result = $this->call('getgroupinformation', array('groupId' => $id));
		return Group::createFromRawObject($result->group);
	}
}
?>