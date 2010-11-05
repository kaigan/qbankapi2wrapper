<?php
	require_once 'QBankAPI.php';
	
	require_once 'model/Object.php';
	require_once 'model/ImageTemplate.php';
	
	/**
	 * Provides functionality for objects in QBank.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 */
	class QBankObjectAPI extends QBankAPI {
		
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
			return md5($mediaId.'_'.$type);
		}
		
		/**
		 * Gets the original media direct from QBank.
		 * NOTE: This will prompt the user to download the original media.
		 * WARNING: Will send a http-header.
		 * @internal This will work even when an object is not deployed.
		 * @param int $mediaId The mediaId of the object to fetch the original media.
		 * @author Björn Hjortsten
		 * @return void
		 */
		public function getMedia($mediaId) {
			header(sprintf('Location: %s/%s/getMedia.php?hash=%s&id=%d', $this->apiAddress, $this->qbankAddress, $this->hash, $mediaId));
		}
		
		/**
		 * Gets a property type from QBank.
		 * @param string $systemName The name of the property type.
		 * @throws CommunicationException Thrown if something went wrong while getting the property type.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return PropertyType
		 */
		public function getPropertyType($systemName) {
			$result = $this->getPropertyTypes(array($systemName));
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
				//TODO fetch all from category
				$data[''] = $param;
			} else {
				//TODO fetch all
			}
			$result = $this->call('getPropertyTypes', $data);
			foreach ($result->propertyTypes as $propertyType) {
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
					$templates[$template->templatename] = new ImageTemplate(strval($template->templatename), intval($template->width), intval($template->height), strval($template->filetype),
																			$aspect, intval($template->quality), intval($template->resolution));
				}
			}
			return $templates;
		}
	}
?>