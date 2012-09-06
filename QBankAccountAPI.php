<?php
require_once 'QBankAPI.php';

require_once 'model/Group.php';
	
class QBankAccountAPI extends QBankAPI {
	
	public function getGroups() {
		$result = $this->call('getgroups', array());
		$groups = array();
		foreach ($result->groups as $group) {
			$groups[] = Group::createFromRawObject($group);
		}
		return $groups;
	}
	
	public function getGroup($id) {
		$result = $this->call('getgroupinformation', array('groupId' => $id));
		return Group::createFromRawObject($result->group);
	}
}
?>