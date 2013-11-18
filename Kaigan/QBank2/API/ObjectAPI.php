<?php

namespace Kaigan\QBank2\API;

use \InvalidArgumentException;
use \Kaigan\QBank2\API\Exception\CommunicationException;
use \Kaigan\QBank2\API\Exception\ConnectionException;
use \Kaigan\QBank2\API\Exception\PropertyException;
use \Kaigan\QBank2\API\Model\Category;
use \Kaigan\QBank2\API\Model\DeploymentSite;
use \Kaigan\QBank2\API\Model\ImageTemplate;
use \Kaigan\QBank2\API\Model\Object;
use \Kaigan\QBank2\API\Model\PropertyType;
use \stdClass;
	
/**
 * Provides functionality for objects in QBank.
 * @author Björn Hjortsten
 * @copyright Kaigan 2010
 */
class ObjectAPI extends BaseAPI {

	/**
	 * Gets an object from QBank
	 * @param int $id The id of the object.
	 * @throws CommunicationException Thrown if something went wrong while getting the object.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @author Björn Hjortsten
	 * @return Object
	 */
	public function getObject($id) {
		$result = $this->call('getobjectinformation', array('objectId' => $id));
		return Object::createFromRawObject($result->data);
	}

	/**
	 * Gets the hashed filename of an object minus the extension (normally .jpg for everything but original).
	 * @param int $mediaId The media id of an object.
	 * @param string $type The image type id. Standard types are 'original', 'medium' and 'thumb'.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public static function getHashedFilename($mediaId, $type = 'original') {
		if ($type == 'original') {
			return md5($mediaId);
		}
		return md5($mediaId.'_'.$type);
	}

	/**
	 * Forces download of a file. Displaces the file as an attachment to the page.
	 * WARNING: Will close the current session if there is one.
	 * @param string $pathToFile The path to the file to force download of.
	 * @param string $filename The filename to present to the user.
	 * @param string $mimetype The mime-type to present the file as.
	 * @author Björn Hjortsten
	 * @return bool True if the download went ok. False if not.
	 */
	public static function forceDownload($pathToFile, $filename, $mimetype = 'application/octet-stream') {
		session_write_close();
		@ob_end_clean();
		$realPath = realpath($pathToFile);
		if ($realPath === false) {
			error_log(sprintf('Error while trying to force download of the path %s with the name %s. It is not a file!', $pathToFile, $filename));
			return false;
		} else {
			$pathToFile = $realPath;
		}
		if (!is_file($pathToFile)) {
			error_log(sprintf('Error while trying to force download of the path %s with the name %s. It is not a file!', $pathToFile, $filename));
			return false;
		}
		if (connection_status() != 0) {
			error_log(sprintf('Error while trying to force download of the path %s with the name %s. No connection to client!', $pathToFile, $filename));
			return false;
		}
		set_time_limit(0);

		/*if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			$filename = preg_replace('/\./', '%2e', $name, substr_count($filename, '.') - 1);
		}*/

		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Type: '.$mimetype);
		error_log("$realPath");
		header('X-SendFile: '.$realPath);
		die;

		/*
		if ($fileHandle = fopen($pathToFile, 'rb')) {
			while ((!feof($fileHandle)) && (connection_status() == 0)) {
				print(fread($fileHandle, 1024*8));
				flush();
			}
			fclose($fileHandle);
		}
		*/

		return ((connection_status() == 0) and !connection_aborted());
	}

	/**
	 * Gets the original media direct from QBank.
	 * NOTE: If the type is "original", this will prompt the user to download the original media.
	 * WARNING: Will send a http-header.
	 * @internal This will work even when an object is not deployed.
	 * @param int $mediaId The mediaId of the object to fetch the original media.
	 * @param string $type The image type id. Standard types are 'original', 'medium' and 'thumb'.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function getMedia($mediaId, $type = 'original', $redirect = true, $moodboardHash = null) {
		if (is_numeric($type)) {
			$type = intval($type);
			$url = sprintf('%s/%s/getMedia?hash=%s&id=%d&templateId=%d', $this->apiAddress, $this->qbankAddress, $this->hash, $mediaId, $type);
		} else {
			$url = sprintf('%s/%s/getMedia?hash=%s&id=%d&type=%s', $this->apiAddress, $this->qbankAddress, $this->hash, $mediaId, $type);
		}
		if ($moodboardHash != null) {
			$url .= '&mhash='.$moodboardHash;
		}
		if ($redirect == true) {
			header('Location: '.$url);
		} else {
			return $url;
		}
	}

	/**
	 * Uploads a new file to QBank.
	 * @param int $categoryId The category that the object should belong to.
	 * @param string $name The name of the new object.
	 * @param string $pathToFile The path to the file to upload.
	 * @param array $properties An array of {@link PropertyBase}s. Defines property values of the new object.
	 * @throws InvalidArgumentException Thrown if $categoryId is not a number or if $pathToFile is invalid.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @throws CommunicationException Thrown if something went wrong while communicating with QBank.
	 * @author Björn Hjortsten
	 * @return Object The newly created object in QBank.
	 */
	public function upload($categoryId, $name, $pathToFile, array $properties = array()) {
		$function = 'createobject';
		if (!is_numeric($categoryId)) {
			throw new InvalidArgumentException('Category id is not a number!');
		}
		$path = realpath($pathToFile);
		if (!is_file($path)) {
			throw new InvalidArgumentException('The supplied path "'.$pathToFile.'" is not a path to a file!');
		}
		if (!is_readable($path)) {
			throw new InvalidArgumentException('The supplied path "'.$pathToFile.'" is not readable!');
		}
		$data = array();
		$data['categoryId'] = (int)$categoryId;
		$data['name'] = (string)$name;
		$properties = $this->prepareProperties($properties);
		if (!empty($properties)) {
			$data['properties'] = $properties;
		}
		$result = $this->call('createobject', $data, true, $path);

		$object = $this->getObject($result->objectId);
		return $object;
	}

	/**
	 * Uploads a file as a new version of an already uploaded object.
	 * @param int $objectId The id of the object to replace.
	 * @param string $pathToFile A path to the file to upload.
	 * @throws InvalidArgumentException Thrown if $objectId is not numeric.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @throws CommunicationException Thrown if something went wrong while communicating with QBank.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function newVersion($objectId, $pathToFile) {
		if (!is_numeric($objectId)) {
			throw new InvalidArgumentException('Object id is not a number!');
		}
		$this->call('createnewversion', array('objectId' => $objectId), true, $pathToFile);
	}

	/**
	 * Save values to properties.
	 * Remember: With great power comes great responsibility.
	 * @param int $objectId The id of the object.
	 * @param array $properties An array of {@link PropertyBase}s.
	 * @param int $languageId The id of the language the values are in.
	 * @throws InvalidArgumentException Thrown if Either the objectId or languageId is not numeric.
	 * @throws CommunicationException Thrown if something went wrong while getting the property type.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @author Björn Hjortsten
	 * @return Object The object with the new property values set.
	 */
	public function saveProperties($objectId, array $properties, $languageId = null) {
		if (!is_numeric($objectId)) {
			throw new InvalidArgumentException('Object id is not a number!');
		}
		$data['objectId'] = intval($objectId);
		$properties = $this->prepareProperties($properties);
		if (!empty($properties)) {
			$data['properties'] = $properties;
		}
		if ($languageId != null) {
			if (!is_numeric($languageId)) {
				throw new InvalidArgumentException('Language id is not numeric!');
			}
			$data['languageId'] = intval($languageId);
		}
		$this->call('editobject', $data);
		return $this->getObject($objectId);
	}

	/**
	 * Saves a new value as the Objects name.
	 * @param int $objectId The id of the object.
	 * @param string $name The new name of the object.
	 * @author Björn Hjortsten
	 * @throws InvalidArgumentException Thrown if the objectId is not numeric.
	 * @throws CommunicationException Thrown if something went wrong while saving the name.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @return Object The object with the new name set.
	 */
	public function saveName($objectId, $name) {
		if (!is_numeric($objectId)) {
			throw new InvalidArgumentException('Object id is not a number!');
		}
		$data = array();
		$data['objectId'] = intval($objectId);
		$data['information'] = array('name' => $name);

		$this->call('editobject', $data);
		return $this->getObject($objectId);
	}

	/**
	 * Gets all {@link Category}(ies) from QBank.
	 * @author Björn Hjortsten
	 * @return array An array of {@link Category}.
	 */
	public function getCategories() {
		$results = $this->call('getcategories', array());
		$categories = array();
		if (is_array($results->categories)) {
			foreach ($results->categories as $result) {
				$categories[] = new Category($result->id, $result->name);
			}
		}
		return $categories;
	}

	/**
	 * Gets a property type from QBank.
	 * @param string $systemName The name of the property type.
	 * @throws PropertyException Thrown if the property type does not exist.
	 * @throws CommunicationException Thrown if something went wrong while getting the property type.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @author Björn Hjortsten
	 * @return PropertyType
	 */
	public function getPropertyType($systemName) {
		$result = $this->getPropertyTypes(array($systemName));
		if (empty($result[$systemName])) {
			throw new PropertyException('The specified property does not exist!');
		}
		return $result[$systemName];
	}

	/**
	 * Gets several Property types.
	 * @internal Not fully implemented yet. Only an array of system names are respected for now. Other values may cause undefined behaviour.
	 * @param mixed $param Either an array of system names of property types or a category id or null.
	 * @throws CommunicationException Thrown if something went wrong while getting the property types.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @author Björn Hjortsten
	 * @return array An array of {@link PropertyType}s.
	 */
	public function getPropertyTypes($param = null) {
		if (is_array($param)) {
			$data['propertyTypeNames'] = $param;
		} elseif (is_numeric($param)) {
			$data['categoryId'] = intval($param);
		} else {
			// Don't send anything to get everything!
		}
		$result = $this->call('getPropertyTypes', $data);
		foreach ($result->propertyTypes as $propertyName => $propertyType) {
			if (empty($propertyType)) {
				trigger_error('Skipping property type "'.$propertyName.'". Probably does not exist.', 'warning');
				continue;
			}
			$propertyTypes[$propertyType->propertyName] = PropertyType::createFromRawObject($propertyType);
		}
		return $propertyTypes;
	}

	/**
	 * Gets all {@link ImageTemplate}s.
	 * @throws CommunicationException Thrown if something went wrong while getting the image templates.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @author Björn Hjortsten
	 * @return array An array of {@link ImageTemplate}s.
	 */
	public function getImageTemplates() {
		$result = $this->call('getimagetemplates', array());
		if (is_array($result->imagetemplates)) {
			foreach ($result->imagetemplates as $template) {
				$aspect = explode('x', $template->aspectratio);
				$aspect = array_filter($aspect);
				if (empty($aspect)) {
					$aspect = null;
				} else {
					$aspect = implode(':', $aspect);
				}
				$tmp = new ImageTemplate(strval($template->templatename), intval($template->width), intval($template->height), strval($template->filetype),
																		$aspect, intval($template->quality), intval($template->resolution));
				$tmp->setId(intval($template->templateId));
				$templates[$template->templatename] = $tmp;
			}
		}
		return $templates;
	}

	/**
	 * Gets an {@link ImageTemplate}.
	 * @param string $name The name of the image template to get.
	 * @throws InvalidArgumentException Thrown if there is no image template with the specified name.
	 * @throws CommunicationException Thrown if something went wrong while getting the image template.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @author Björn Hjortsten
	 * @return ImageTemplate
	 */
	public function getImageTemplate($name) {
		$templates = $this->getImageTemplates();
		if (isset($templates[$name])) {
			return $templates[$name];
		} else {
			throw new InvalidArgumentException(sprintf('No template with the name %s was found', $name));
		}
	}

	/**
	 * Gets deployment information about an object.
	 * @param int $objectId The id of the object to get deployment information about.
	 * @author Björn Hjortsten
	 * @return array An array of {@link DeploymentSite}s.
	 */
	public function getDeploymentInformation($objectId) {
		$results = $this->call('getdeploymentinformation', array('objectId' => $objectId));
		$siteInfo = array();
		if (is_array($results->sites) && !empty($results->sites)) {
			foreach ($results->sites as $result) {
				$siteInfo[] = DeploymentSite::createFromRawObject($result);
			}
		}
		return $siteInfo;
	}

	/**
	 * Deploys objects to a remote (outside QBank) location.
	 * @param int $siteId The id of the remote location.
	 * @param array $objectIds The ids of the objects to deploy.
	 * @param bool $asynchronous Whether to deploy asynchronously. The method will return immediately if true.
	 * @param stdClass $result Will contain the result if called synchronously.
	 * @author Björn Hjortsten
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @throws CommunicationException Thrown if something went wrong while deploying.
	 * @return void
	 */
	public function deploy($siteId, array $objectIds, $asynchronous = false, &$result = null) {
		if ($asynchronous == true) {
			foreach ($objectIds as $objectId) {
				$this->callAsync('deploy', array('siteId' => $siteId, 'objectIds' => $objectIds));
			}
		} else {
			$result = $this->call('deploy', array('siteId' => $siteId, 'objectIds' => $objectIds));
		}
	}

	/**
	 * Undeploys objects from a remote (outside QBank) location.
	 * @param int $siteId The id of the remote location.
	 * @param array $objectIds The ids of the objects to undeploy.
	 * @param bool $asynchronous Whether to undeploy asynchronously. The method will return immediately if true.
	 * @param stdClass $result Will contain the result if called synchronously.
	 * @author Björn Hjortsten
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @throws CommunicationException Thrown if something went wrong while deploying.
	 * @return void
	 */
	public function undeploy($siteId, array $objectIds, $asynchronous = false, &$result = null) {
		if ($asynchronous == true) {
			foreach ($objectIds as $objectId) {
				$this->callAsync('undeploy', array('siteId' => $siteId, 'objectIds' => $objectIds));
			}
		} else {
			$result = $this->call('undeploy', array('siteId' => $siteId, 'objectIds' => $objectIds));
		}
	}

	/**
	 * Creates a group of objects.
	 * @param int $parentId The id of the {@link SimpleObject} that should be the parent of the group.
	 * @param array $childIds An array of ids to {@link SimpleObject}s that should belong to the group.
	 * @author Björn Hjortsten
	 * @throws CommunicationException Thrown if something went wrong while creating the group.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @return boolean Returns true if the group of objects has been created.
	 */
	public function createRelation($parentId, array $childIds) {
		$result = $this->call('createrelation', array('motherId' => $parentId, 'childIds' => $childIds));
		return true;
	}

	/**
	 * Removes objects from a group.
	 * @param int $parentId The id of the {@link SimpleObject} that is the parent of the group. 
	 * @param array $childIds An array of ids to {@link SimpleObject}s that should be removed from the group.
	 * @author Björn Hjortsten
	 * @throws CommunicationException Thrown if something went wrong while creating the group.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @return boolean Returns true if the group of objects has been created.
	 */
	public function removeRelation($parentId, array $childIds) {
		$this->call('removerelation', array('motherId' => $parentId, 'childIds' => $childIds));
		return true;
	}

	/**
	 * Encodes an array of {@link PropertyBase}s for transmission to the API.
	 * @param array $properties The array to be encoded.
	 * @author Björn Hjortsten
	 * @return array An array ready for transport.
	 */
	private function prepareProperties(array $properties) {
		if (is_array($properties)) {
			$props = array();
			foreach ($properties as $property) {
				if (is_a($property, 'PropertyBase')) {
					$props[$property->getSystemName()] = $property->getValue();
				} else {
					error_log(sprintf('[%s] (%s) %s: %s'."\n",date('Y-m-d H:i:s'), 'INFO', $this->qbankAddress.'/'.$function, 'Skipping bad value '.@strval($property)), 3, QBankAPI::CALLS_LOG);
				}
			}
			return $props;
		}
	}
}
