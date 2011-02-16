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
	}
?>