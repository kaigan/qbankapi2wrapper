<?php
	/**
	 * Represents a request for properties when searching.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2011
	 * @package QBankAPIWrapper
	 */
	class PropertyRequest extends PropertyCriteria {
		
		/**
		 * Creates a PropertyRequest.
		 * @param string $systemName The system name of the property.
		 * @author Björn Hjortsten
		 * @return PropertyRequest
		 */
		public function __construct($systemName) {
			parent::__construct($systemName, null, PropertyCriteria::EQUAL, true);
		}
	}
?>