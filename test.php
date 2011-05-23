<?php
	require_once 'QBankFolderAPI.php';
	require_once 'QBankObjectAPI.php';
	require_once 'QBankSearchAPI.php';
	require_once 'QBankMoodboardAPI.php';
	
	error_reporting(E_ALL);
	header('Content-type: text/html; charset="utf-8"');
	
	// Creating folder API 
	echo '<h1>Testing folders</h1>';
	$folderApi = new QBankFolderAPI('demo26.qbank.se');
	
	// Logging in
	if ($folderApi->login('apiuser', '6FHyw0sYyI7cfHWb') !== true) {
		die('X Login failed!');
	} else {
		echo '✓ Logged in!<br />';
	}
	
	//Getting hash
	$hash = $folderApi->getHash();
	
	// Getting all folders
	$folders = $folderApi->getFolders(null,null, true);
	if (is_array($folders)) {
		foreach ($folders as $folder) {
			$names[] = $folder->getName();
		}
		echo sprintf('✓ Got %d root folder(s): %s<br />', count($folders), implode(', ', $names));
	} else {
		echo 'X Got no folders, check for error<br />';
	}
	
	// Creating folder tree
	try {
		$folders = Folder::createTree($folders);
		echo '✓ Created folder tree<br />';
	} catch (FolderException $fe) {
		echo 'X Failed to create folder tree<br />';
	}
	
	// Getting specific folder
	$folder = $folderApi->getFolder(next($folders)->getId(), false, true);
	if (@get_class($folder) == 'Folder') {
		echo '✓ Got single folder with children<br />';
	} else {
		echo 'X Could not get single folder<br />';
	}
	
	// Creating folder
	$folder = $folderApi->createFolder('Testmapp via API');
	if (@get_class($folder) == 'SimpleFolder') {
		echo sprintf('✓ Created folder with name %s and id %d<br />', $folder->getName(), $folder->getId());
	} else {
		echo 'X Failed while creating folder<br />';
	}
	
	// Editing folder
	$folder = $folderApi->editFolder($folder->getId(), $folder->getName().' v.2');
	if (@get_class($folder) == 'SimpleFolder') {
		echo sprintf('✓ Edited the folder with id %d; now called %s<br />', $folder->getId(), $folder->getName());
	} else {
		echo 'X Failed while editing folder<br />';
	}
	
	// Deleting folder
	if ($folderApi->deleteFolder($folder->getId()) !== true) {
		echo sprintf('X Failed to delete the folder with id: %d<br />', $folder->getId());
	} else {
		echo sprintf('✓ Deleted the folder with id: %d<br />', $folder->getId());
	}
	
	// Searching
	echo '<h1>Testing search</h1>';
	$searchApi = new QBankSearchAPI('demo26.qbank.se');
	$searchApi->setHash($hash);
	$result = $searchApi->search('Test',null,null,null,null,1,30,SearchOrder::ID_DESCENDING, true, true);
	
	foreach ($result as $res) {
		$objects[] = 'ID:'.$res->getId().' Name:'.$res->getName();
	}
	
	echo sprintf('✓ Found %d object(s) in %f seconds. Showing %d: %s<br />', $result->getTotalNumberOfResults(), $result->getTimeSpentSearching(), count($result), implode(', ', $objects));
	
	
	echo '<h2>New search</h2>';
	$search = new Search();
	$search->setFreeText('Test');
	$search->setAdvancedObjects(true);
	$result = $searchApi->execute($search);
	
	foreach ($result as $res) {
		$objects[] = 'ID:'.$res->getId().' Name:'.$res->getName();
	}
	
	echo sprintf('✓ Found %d object(s) in %f seconds. Showing %d: %s<br />', $result->getTotalNumberOfResults(), $result->getTimeSpentSearching(), count($result), implode(', ', $objects));
	
	// Creating object API
	echo '<h1>Testing objects</h1>';
	$objectApi = new QBankObjectAPI('demo26.qbank.se');
	$objectApi->setHash($hash);
	
	// Getting object
	$object = $objectApi->getObject($result[0]->getId());
	if ($object->getId() == $result[0]->getId()) {
		echo '✓ Got object<br />';
	} else {
		printf('X Failed to get correct object. Wanted %d, got %d<br />', $result[0]->getId(), $object->getId());
	}
	
	// Getting property types
	$result = $objectApi->getPropertyType('system_media_status');
	if (get_class($result) == 'PropertyType' && $result->getSystemName() == 'system_media_status') {
		echo '✓ Got property type<br />';
	} else {
		echo 'X Failed to get property type';
	}
	
	//Putting Object in folder
	$folder = $folderApi->createFolder('Testmapp via API');
	if ($folderApi->addObjectToFolder($folder->getId(), $object->getId())) {
		echo '✓ Put an object in a folder <br />';
	} else {
		echo 'X Failed to put an object in a folder<br />';
	}
	
	//Removing an object from a folder
	if ($folderApi->removeObjectFromFolder($folder->getId(), $object->getId())) {
		echo '✓ Removed an object from a folder<br />';
	} else {
		echo 'X Failed to remove an object from a folder<br />';
	}
	$folderApi->deleteFolder($folder->getId());
	
	//Getting image templates
	$result = $objectApi->getImageTemplates();
	if (is_array($result) && !empty($result)) {
		$template = current($result);
		echo '✓ Got some image templates. First one: '.$template->getName().' '.$template->getMaxWidth().'x'.$template->getMaxHeight().'<br />';
	} else {
		echo 'X Failed to get any image templates!<br />';
	}
	
	// Getting deployment info
	$result = $objectApi->getDeploymentInformation($object->getId());
	var_dump($result);
	
	// Uploading file
	$object = $objectApi->upload(7, 'QBankAPI', 'QBankAPI.php', array(new PropertyBase('description', 'This is some source code!')));
	var_dump($object);
	
	// Moodboards
	echo '<h1>Testing moodboards</h1>';
	$moodboardApi = new QBankMoodboardAPI('demo26.qbank.se');
	$moodboardApi->setHash($hash);
	
	// Getting Moodboards
	$result = $moodboardApi->getMoodboards();
	if (is_array($result) && !empty($result)) {
		$moodboard = current($result);
		echo '✓ Found some Moodboards ('.count($result).'). Showing the first one: '.$moodboard->getName().' (#'.$moodboard->getId().') expires '.strftime('%F', $moodboard->getExpirationDate()).', created '.strftime('%F', $moodboard->getCreationTime()).' with hash: '.$moodboard->getHash().'<br />';
		echo '&nbsp;&nbsp;<a href="'.$moodboardApi->getMoodboardUrl($moodboard).'">Link!</a><br />';
	} else {
		echo 'X Found no Moodboards!<br />';
	}
	
	// Getting Moodboard templates
	$result = $moodboardApi->getMoodboardTemplates();
	if (is_array($result) && !empty($result)) {
		$template = current($result);
		$templateId = key($result);
		echo '✓ Found some templates ('.count($result).'). Showing the first one: '.$template.' (#'.$templateId.') <br />';
	} else {
		echo 'X Found no templates!<br />';
	}
	
	// Creating moodboard
	$result = $moodboardApi->createMoodboard('TestMoodboard from API', '2020-10-10', $templateId, 'Swedish', null, array($object->getId()));
	if (!empty($result)) {
		echo '✓ Created a Moodboard: '.$result->getName().' (#'.$result->getId().') expires '.strftime('%F', $result->getExpirationDate()).', created '.strftime('%F', $result->getCreationTime()).' with hash: '.$result->getHash().'<br />';
	} else {
		echo 'X Could not create a Moodboard! <br />';
	}
	
?>