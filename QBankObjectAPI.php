<?php
	require_once 'QBankAPI.php';
	
	require_once 'model/Object.php';
	
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
		 * Gets the hashed filename of an object.
		 * @param int $mediaId The media id of an object.
		 * @param string $type The image type id. Standard types are 'original', 'medium' and 'thumb'.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public static function getHashedFilename($mediaId, $type = 'original') {
			return md5($mediaId.'_'.$type);
		}
	}
?>