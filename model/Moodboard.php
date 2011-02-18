<?php

	/**
	 * Represents a Moodboard in QBank.
	 * @author Björn Hjortsten
	 * @copyright KaiganTBK 2011
	 * @package QBankAPIWrapper
	 */
	class Moodboard {
		
		/**
		 * The id of the Moodboard
		 * @var int
		 */
		protected $id;
		
		/**
		 * The name of the Moodboard
		 * @var string
		 */
		protected $name;
		
		/**
		 * The time the Moodboard will expire.
		 * @var int
		 */
		protected $expirationDate;
		
		/**
		 * The identifying hash of the Moodboard.
		 * @var string
		 */
		protected $hash;
		
		/**
		 * The Moodboards time of creation.
		 * @var int
		 */
		protected $creationTime;
		
		/**
		 * Creates a new Moodboard.
		 * @param int $id The Moodboards id.
		 * @param string $name The Moodboards name.
		 * @param int $expirationDate The time the Moodboard will expire.
		 * @author Björn Hjortsten
		 * @return Moodboard
		 */
		public function __construct($id, $name, $expirationDate) {
			$this->id = intval($id);
			$this->name = $name;
			$this->expirationDate = strtotime($expirationDate);
		}
		
		/**
		 * Gets the id of the Moodboard.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getId() {
			return $this->id;
		}
		
		/**
		 * Gets the name of the Moodboard.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}
		
		/**
		 * Gets the time the Moodboard will expire.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getExpirationDate() {
			return $this->expirationDate;
		}
		
		public function getHash() {
			return $this->hash;
		}
		
		public function getCreationTime() {
			return $this->creationTime;
		}
		
		/**
		 * Creates a Mooboard from an object directly from a call to the API.
		 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
		 * @param stdClass $rawObject
		 * @author Björn Hjortsten
		 * @return Moodboard
		 */
		public static function createFromRawObject($rawObject) {
			$moodboard = new Moodboard($rawObject->moodboardId, $rawObject->moodboardName, $rawObject->expireDate);
			$moodboard->hash = strval($rawObject->hash);
			$moodboard->creationTime = strtotime($rawObject->createdDate);
			return $moodboard;
		}
	}
?>