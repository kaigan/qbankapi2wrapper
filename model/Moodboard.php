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
		 * Some header text.
		 * @var string
		 */
		protected $headerText;
		
		/**
		 * Some footer text
		 * @var string
		 */
		protected $footerText;
		
		/**
		 * Notes about the moodboard.
		 * @var string
		 */
		protected $notes;
		
		/**
		 * The email address to which notices about the moodboard should be sent.
		 * @var string
		 */
		protected $email;
		
		/**
		 * The default size of thumbnails.
		 * @var string
		 */
		protected $defaultThumbSize;
		
		/**
		 * The pincode
		 * @var string
		 */
		protected $pincode;
		
		/**
		 * Whether search is enabled or not.
		 * @var bool
		 */
		protected $searchEnabled;
		
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
		
		/**
		 * Gets the identifying hash of the moodboard.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getHash() {
			return $this->hash;
		}
		
		/**
		 * Gets the time of creation of the moodboard.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getCreationTime() {
			return $this->creationTime;
		}
		
		/**
		 * Gets the moodboards header text.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getHeaderText() {
			return $this->headerText;
		}
		
		/**
		 * Gets the moodboards footer text.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getFooterText() {
			return $this->footerText;
		}
		
		/**
		 * Gets the moodboards notes.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getNotes() {
			return $this->notes;
		}
		
		/**
		 * Gets the email address to which notices about the moodboard should be sent.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getEmail() {
			return $this->email;
		}
		
		/**
		 * Gets the default thumbnail size.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getDefaultThumbSize() {
			return $this->defaultThumbSize;
		}
		
		/**
		 * Gets the pincode.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getPinCode() {
			return $this->pincode;
		}
		
		/**
		 * Tells whether search is enabled or not.
		 * @author Björn Hjortsten
		 * @return bool
		 */
		public function isSearchEnabled() {
			return $this->searchEnabled;
		}
		
		/**
		 * Creates a Mooboard from an object directly from a call to the API.
		 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
		 * @param stdClass $rawObject
		 * @author Björn Hjortsten
		 * @return Moodboard
		 */
		public static function createFromRawObject($rawObject) {
			var_dump($rawObject);
			$moodboard = new Moodboard($rawObject->moodboardId, $rawObject->moodboardName, $rawObject->expireDate);
			$moodboard->hash = strval($rawObject->hash);
			$moodboard->creationTime = strtotime($rawObject->createdDate);
			$moodboard->headerText = strval(trim($rawObject->headerText));
			$moodboard->footerText = strval(trim($rawObject->footerText));
			$moodboard->notes = strval(trim($rawObject->notes));
			$moodboard->email = strval(trim($rawObject->email));
			$moodboard->defaultThumbSize = strval(trim($rawObject->listtype));
			$moodboard->pincode = $rawObject->pincode;
			$moodboard->searchEnabled = (bool)$rawObject->search;
			return $moodboard;
		}
	}
?>