<?php
	require_once 'QBankAPI.php';
	require_once 'model/Moodboard.php';
	
	/**
	 * Provides functionality for Moodboards in QBank.
	 * @author Björn Hjortsten
	 * @copyright KaiganTBK 2011
	 * @package QBankAPIWrapper
	 */
	class QBankMoodboardAPI extends QBankAPI {
		
		/**
		 * Gets all the Moodboards from QBank.
		 * @throws CommunicationException Thrown if something went wrong while getting the image template.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return array An array of {@link Moodboard}s. Null if there are no results.
		 */
		public function getMoodboards() {
			$result = $this->call('getmoodboards', array());
			if (is_array($result->moodboards) && !empty($result->moodboards)) {
				foreach ($result->moodboards as $moodboard) {
					$moodboards[$moodboard->hash] = Moodboard::createFromRawObject($moodboard);
				}
			}
			return $moodboards;
		}
		
		/**
		 * Gets a specific Moodboard from QBank specified by a hash.
		 * @param string $hash The hash that specifies which Moodboard to get.
		 * @throws CommunicationException Thrown if something went wrong while getting the Moodboard or there are no 
		 * Moodboards with the specified hash.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return Moodboard The {@link Moodboard} with the specified hash.
		 */
		public function getMoodboard($hash) {
			$result = $this->call('getmoodboard', array('moodboardHash' => $hash));
			return Moodboard::createFromRawObject($result->moodboard);
		}
		
		/**
		 * Gets all the templates for Moodboards from QBank.
		 * @throws CommunicationException Thrown if something went wrong while getting the image template.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return array An array where the key is the id and the value is the name of the Moodboard.
		 */
		public function getMoodboardTemplates() {
			$result = $this->call('getmoodboardtemplates', array());
			$templates = array();
			if (is_array($result->templates)) {
				foreach ($result->templates as $template) {
					$templates[$template->id] = $template->name;
				}
			}
			return $templates;
		}
		
		/**
		 * Create a Moodboard in QBank.
		 * @param string $name The name of the Moodboard.
		 * @param string $expirationDate The date when the moodboard will seize to function. On the format YYYY-MM-DD.
		 * @param int $templateId The id of the Moodboard template to use.
		 * @param string $language The language of the moodboard. Eg. "Swedish" or "English".
		 * @param string $pincode The password to protect the Moodboard.
		 * @param array $objectIds The ids of the objects that should belong to the Moodboard.
		 * @param string $headerText Some text to display before the content of the moodboard.
		 * @param string $footerText Some text to display after the content of the moodboard.
		 * @param string $notes Private notes about the moodboard.
		 * @throws InvalidArgumentException Thrown if $expirationDate is not a valid date.
		 * @throws CommunicationException Thrown if something went wrong while getting the image template.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return Moodboard The newly created Moodboard.
		 */
		public function createMoodboard($name, $expirationDate, $templateId, $language, $pincode = null,
										array $objectIds = array(), $headerText = null, $footerText = null,
										$notes = null) {
			$data['name'] = strval($name);
			if (!preg_match('/\d{4}-\d{2}-\d{2}/', $expirationDate) && is_numeric($expirationDate)) {
				$data['expirationDate'] = strftime('%F', intval($expirationDate));
			} else {
				$data['expirationDate'] = strftime('%F', strtotime($expirationDate));
			}
			if ($data['expirationDate'] === false) {
				throw new InvalidArgumentException($expirationDate.' is not a valid date!');
			}
			$data['templateId'] = intval($templateId);
			$data['language'] = strval($language);
			if (!empty($pincode)) {
				$data['pincode'] = strval($pincode);
			}
			if (!empty($objectIds)) {
				$data['objectIds'] = array();
				foreach ($objectIds as $objectId) {
					if (is_numeric($objectId)) {
						$data['objectIds'][] = intval($objectId);
					}
				}
			}
			if (!empty($headerText)) {
				$data['headerText'] = strval($headerText);
			}
			if (!empty($footerText)) {
				$data['footerText'] = strval($footerText);
			}
			if (!empty($notes)) {
				$data['notes'] = strval($notes);
			}
			$result = $this->call('createmoodboard', $data, true);
			return Moodboard::createFromRawObject($result->moodboard);
		}
		
		/**
		 * Adds objects to a Moodboard
		 * @param int $moodboardId The Moodboard that the objects should be added to.
		 * @param array $objectIds An array of integers. The objects to add.
		 * @throws CommunicationException Thrown if something went wrong while getting the image template.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return void
		 */
		public function addToMoodboard($moodboardId, array $objectIds) {
			$data['moodboardId'] = intval($moodboardId);
			$data['objectIds'] = $objectIds;
			$this->call('addtomoodboard', $data);
		} 
		
		/**
		 * Gets a url to a Moodboard.
		 * @param Moodboard $moodboard
		 * @author Björn Hjortsten
		 * @return string An url.
		 */
		public function getMoodboardUrl(Moodboard $moodboard) {
			return 'http://'.$this->qbankAddress.'/v2.6/mb.php?h='.$moodboard->getHash().'&amp;r=y';
		}
	}
?>