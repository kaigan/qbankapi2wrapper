<?php
	require_once 'QBankAPI.php';
	
	require_once 'model/Object.php';
	require_once 'model/ImageTemplate.php';
	require_once 'model/DeploymentSite.php';
	
	/**
	 * Provides functionality for objects in QBank.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @package QBankAPIWrapper
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
    		
    		if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        		$filename = preg_replace('/\./', '%2e', $name, substr_count($filename, '.') - 1);
    		}
    		
    		header('Cache-Control: ');
		    header('Pragma: ');
		    header('Content-Type: '.$mimetype);
		    header('Content-Length: ' .(string)(filesize($pathToFile)) );
		    header('Content-Disposition: attachment; filename="'.$filename.'"');
		    header('Content-Transfer-Encoding: binary'."\n");
		    
		    if ($fileHandle = fopen($pathToFile, 'rb')) {
		        while ((!feof($fileHandle)) && (connection_status() == 0)) {
		            print(fread($fileHandle, 1024*8));
		            flush();
		        }
		        fclose($fileHandle);
		    }
		    
		    return ((connection_status() == 0) and !connection_aborted());
		}
		
		/**
		 * Gets the original media direct from QBank.
		 * NOTE: This will prompt the user to download the original media.
		 * WARNING: Will send a http-header.
		 * @internal This will work even when an object is not deployed.
		 * @param int $mediaId The mediaId of the object to fetch the original media.
		 * @param string $type The image type id. Standard types are 'original', 'medium' and 'thumb'.
		 * @author Björn Hjortsten
		 * @return void
		 */
		public function getMedia($mediaId, $type = 'original') {
			header(sprintf('Location: %s/%s/getMedia?hash=%s&id=%d&type=%s', $this->apiAddress, $this->qbankAddress, $this->hash, $mediaId, $type));
		}
		
		/**
		 * Uploads a new file to QBank.
		 * @param int $categoryId The category that the object should belong to.
		 * @param string $name The name of the new object.
		 * @param string $pathToFile The path to the file to upload.
		 * @param array $properties An array of {@link PropertyBase}s. Defines proåerty values of the new object.
		 * @throws InvalidArgumentException Thrown if $categoryId is not a number or if $pathToFile is invalid.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @throws CommunicationException Thrown if something went wrong while communicating with QBank.
		 * @author Björn Hjortsten
		 * @return Object The newly created object in QBank.
		 */
		public function upload($categoryId, $name, $pathToFile, array $properties = null) {
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
			$data['hash'] = $this->hash;
			$data['categoryId'] = intval($categoryId);
			$data['name'] = strval($name);
			if (is_array($properties)) {
				$props = array();
				foreach ($properties as $property) {
					if (is_a($property, 'PropertyBase')) {
						$props[$property->getSystemName()] = $property->getValue();
					} else {
						error_log(sprintf('[%s] (%s) %s: %s'."\n",date('Y-m-d H:i:s'), 'INFO', $this->qbankAddress.'/'.$function, 'Skipping bad value '.@strval($property)), 3, QBankAPI::CALLS_LOG);
					}
				}
				if (!empty($props)) {
					$data['properties'] = $props;
				}
			}
			$json = json_encode($data);
			$data = array(
				'data' => $json,
				'userfile' => '@'.$path
			);
			$url = sprintf('%s/%s/%s', $this->apiAddress, $this->qbankAddress, $function);
			curl_setopt($this->curlHandle, CURLOPT_URL, $url);
			curl_setopt($this->curlHandle, CURLOPT_POST, true);
			curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $data);
			curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->curlHandle, CURLOPT_FAILONERROR, true);
			curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, $this->requestTimeout);
			if ($this->useSSL === true) {
				curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
			}
			curl_setopt($this->curlHandle, CURLOPT_USERAGENT, 'QBankAPIWrapper '.QBankAPI::VERSION);
			error_log(sprintf('[%s] (%s) %s: %s'."\n",date('Y-m-d H:i:s'), 'UPLOAD', $this->qbankAddress.'/'.$function, $json), 3, QBankAPI::CALLS_LOG);
			$resultJSON = curl_exec($this->curlHandle);
			if ($resultJSON === false) {
				$error = sprintf('Error while comunicating with QBank: %s', curl_error($this->curlHandle));
				curl_close($this->curlHandle);
				$this->curlHandle = curl_init();
				throw new ConnectionException($error, curl_errno($this->curlHandle));
			}
			$result = json_decode($resultJSON);
			if (!isset($result->success) || $result->success === false) {
				if (isset($result->error)) {
					error_log(sprintf('[%s] (%s) %s: %s'."\n",date('Y-m-d H:i:s'), 'ERROR', $this->qbankAddress.'/'.$function, $json), 3, QBankAPI::CALLS_LOG);
					throw new CommunicationException($result->error->message, $result->error->code, $result->error->type);
				} else {
					error_log(sprintf('[%s] (%s) %s: %s'."\n\t".'Response: %s'."\n", date('Y-m-d H:i:s'), 'UNKNOWN ERROR', $this->qbankAddress.'/'.$function, $json, $resultJSON), 3, QBankAPI::UNKNOWNS_LOG);
					throw new CommunicationException('Unknown error! Non-successful call to QBank API and no specified error. Please note the time and report this to support@kaigantbk.se', 99, 'UnknownError');
				}
			}
			$object = $this->getObject($result->objectId);
			return $object;
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
					$templates[$template->templatename] = new ImageTemplate(strval($template->templatename), intval($template->width), intval($template->height), strval($template->filetype),
																			$aspect, intval($template->quality), intval($template->resolution));
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
	}
?>