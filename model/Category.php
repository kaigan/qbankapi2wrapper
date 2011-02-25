<?php
	
	/**
	 * Represents a category in QBank.
	 * @author Björn Hjortsten
	 * @copyright KaiganTBK AB
	 * @package QBankAPIWrapper
	 */
	class Category {
		
		/**
		 * The id of the category.
		 * @var int
		 */
		protected $id;
		
		/**
		 * The name of the category.
		 * @var string
		 */
		protected $name;
		
		/**
		 * Creates a new category.
		 * @param int $id The id of the category.
		 * @param string $name The name of the category.
		 * @author Björn Hjortsten
		 * @return Category
		 */
		public function __construct($id, $name) {
			$this->id = intval($id);
			$this->name = strval($name);
		}
		
		/**
		 * Gets the id of the category.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getId() {
			return $this->id;
		}
		
		/**
		 * Gets the name of the category.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}
	}
?>