<?php
	/**
	 * Represents a QBank property type.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @package QBankAPIWrapper
	 */
	class PropertyType extends PropertyBase{
		
		/**
		 * The id of the propertys type.
		 * @var int
		 */
		protected $propertyTypeId;
		
		/**
		 * The display name of the property.
		 * @var string
		 */
		protected $title;
		
		/**
		 * If the user should be able to modify the property value.
		 * @internal Probably not used at all.
		 * @var bool
		 */
		protected $editable;
		
		/**
		 * The type of the propertys value and default value.
		 * @var PropertyValueType
		 */
		protected $propertyValueType;
		
		/**
		 * The original value type that was set in QBank.
		 * @var mixed
		 */
		protected $qbankValueType;
		
		/**
		 * The default value of the property.
		 * @var mixed
		 */
		protected $defaultValue;
		
		/**
		 * If this propertys value are multiple values.
		 * @var bool
		 */
		protected $multipleChoice;
		
		/**
		 * If setting of the property is mandatory.
		 * @var mixed
		 */
		protected $mandatory;
		
		/**
		 * If the property should be displayes as a link in QBank.
		 * @internal Probably not used in frontends.
		 * @var mixed
		 */
		protected $link;
		
		/**
		 * If the propertys value is a list of keywords.
		 * @var mixed
		 */
		protected $keywords;
		
		/**
		 * Information about the property.
		 * @var string
		 */
		protected $info;
		
		/**
		 * Creates a new property type.
		 * @param int $id The id of the property.
		 * @param int $propertyTypeId The id of the propertys type.
		 * @param string $systemName The system name of the property.
		 * @param string $title The display name of the property.
		 * @param mixed $value The value of the property.
		 * @param mixed $defaultValue The default value of the property.
		 * @param PropertyValueType $propertyValueType The type of the propertys value and default value.
		 * @param bool $multipleChoice If this propertys value are multiple values.
		 * @param bool $editable If the user should be able to modify the property value. {@internal Probably not used at all. }
		 * @author Björn Hjortsten
		 * @return Property
		 */
		public function __construct($propertyTypeId, $systemName, $title, $value, $defaultValue = null, 
									$propertyValueType = PropertyValueType::QB_String, $multipleChoice = false, $editable = true) {
			parent::__construct($systemName, $value);
			$this->propertyTypeId = $propertyTypeId;
			$this->title = $title;
			$this->defaultValue = $defaultValue;
			$this->propertyValueType = $propertyValueType;
			$this->multipleChoice = $multipleChoice;
			$this->editable = $editable;
		}
		
		/**
		 * Gets the id of the propertys propertytype.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getPropertyTypeId() {
			return $this->propertyTypeId;
		}
		
		/**
		 * Gets the system name of the property.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getSystemName() {
			return $this->systemName;
		}
		
		/**
		 * Gets the display name of the property.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getTitle() {
			return $this->title;
		}
		
		/**
		 * Gets the default value of the property.
		 * @author Björn Hjortsten
		 * @return mixed May be any type. {@link PropertyType::getPropertyValueType()} specifies the type.
		 * @see PropertyValueType
		 */
		public function getDefaultValue() {
			return $this->defaultValue;
		}
		
		/**
		 * Gets the {@link PropertyValueType} of the property.
		 * @author Björn Hjortsten
		 * @return PropertyValueType
		 */
		public function getPropertyValueType() {
			return $this->propertyValueType;
		}
		
		/**
		 * Gets the value type as defined in QBank.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getQBankValueType() {
			return $this->qbankValueType;
		} 
		
		/**
		 * If this propertys value are multiple values.
		 * @author Björn Hjortsten
		 * @return bool True if the propertys value could be multiple.
		 */
		public function isMultipleChoice() {
			return $this->multipleChoice;
		}
		
		/**
		 * If the user should be able to modify the property value.
		 * @internal Probably not used at all.
		 * @author Björn Hjortsten
		 * @return bool True if the user should be able to modify the property value.
		 */
		public function isEditable() {
			return $this->editable;
		}
		
		/**
		 * If the property is mandatory to set.
		 * @author Björn Hjortsten
		 * @return mixed True if the property is mandatory, false if not. Null if it does not apply.
		 */
		public function isMandatory() {
			return $this->mandatory;
		}
		
		/**
		 * If the property is displayed as a link to other objects with the same value in QBank.
		 * @internal Probably not used in frontends.
		 * @author Björn Hjortsten
		 * @return mixed True if the property is displayed as a link, false if not. Null if it does not apply.
		 */
		public function isLink() {
			return $this->link;
		}
		
		/**
		 * If the property is a list of keywords.
		 * @author Björn Hjortsten
		 * @return mixed True if the property is a list of keywords, false if not. Null if it does not apply.
		 */
		public function isKeywords() {
			return $this->keywords;
		}
		
		/**
		 * Gets the information given about the property.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getInfo() {
			return $this->info;
		}
		
		/**
		 * If the property is a system property.
		 * @author Björn Hjortsten
		 * @return bool
		 */
		public function isSystemProperty() {
			if (stristr($this->systemName, 'system_') === false) {
				return false;
			}
			return true;
		}
		
		/**
		 * Creates a {@link PropertyType} from an object directly from a call to the API.
		 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
		 * @param stdClass $rawProperty The raw object from the API-call.
		 * @author Björn Hjortsten
		 * @return Property
		 */
		public static function createFromRawObject(stdClass $rawProperty) {
			$propertyValueType = Property::getPropertyValueTypeFromString($rawProperty->propertyType);
			switch ($propertyValueType) {
				case PropertyValueType::QB_Array:
					if ($rawProperty->multiplechoice == true) {
						$value = explode('|', $rawProperty->value);
						$value = array_filter($value);
					} else {
						$value = strval($rawProperty->value);
					}
					$defaultValue = explode('|', $rawProperty->defaultValue);
					$defaultValue = array_filter($defaultValue);
					break;
				case PropertyValueType::QB_Bool:
					$value = (bool) $rawProperty->value;
					$defaultValue = (bool) $rawProperty->defaultValue;
					break;
				case PropertyValueType::QB_Date:
					$value = strtotime($rawProperty->value);
					$defaultValue = strtotime($rawProperty->defaultValue);
					break;
				case PropertyValueType::QB_Float:
					$value = floatval($rawProperty->value);
					$defaultValue = floatval($rawProperty->defaultValue);
					break;
				case PropertyValueType::QB_Int:
					$value = intval($rawProperty->value);
					$defaultValue = intval($rawProperty->defaultValue);
					break;
				default:
					if (isset($rawProperty->keywords) && (bool) $rawProperty->keywords === true) {
						$value = explode('|', $rawProperty->value);
						$defaultValue = explode('|', $rawProperty->defaultValue);
						$value = array_filter($value);
						$defaultValue = array_filter($defaultValue);
					} else {
						$value = $rawProperty->value;
						$defaultValue = $rawProperty->defaultValue;
					}
					break;
			}
			if (empty($value)) {
				$value = null;
			}
			if (empty($defaultValue)) {
				$defaultValue = null;
			}
			$property = new PropertyType(intval($rawProperty->id), $rawProperty->propertyName, $rawProperty->title, 
									   $value, $defaultValue, $propertyValueType, (bool) $rawProperty->multiplechoice, (bool) $rawProperty->editable);
			$property->qbankValueType = $rawProperty->propertyType;
			if (isset($rawProperty->editable)) {
				$property->editable = (bool) $rawProperty->editable;
			} else {
				$property->editable = null;
			}
			if (isset($rawProperty->mandatory)) {
				$property->mandatory = (bool) $rawProperty->mandatory;
			} else {
				$property->mandatory = null;
			}
			if (isset($rawProperty->keywords)) {
				$property->keywords = (bool) $rawProperty->keywords;
			} else {
				$property->keywords = null;
			}
			if (isset($rawProperty->link)) {
				$property->link = (bool) $rawProperty->link;
			} else {
				$property->link = null;
			}
			if (isset($rawProperty->info)) {
				$property->info = $rawProperty->info;
				if (empty($property->info)) {
					$property->info = null;
				}
			} else {
				$property->info = null;
			}
			return $property;
		}
		
		/**
		 * Interprets a string to a valuetype for a property.
		 * @param string $string The string to be interpreted.
		 * @author Björn Hjortsten
		 * @return PropertyValueType
		 */
		protected static function getPropertyValueTypeFromString($string) {
			switch (strtolower($string)) {
				case 'arr':
					return PropertyValueType::QB_Array;
					break;
				case 'int':
					return PropertyValueType::QB_Int;
					break;
				case 'float':
					return PropertyValueType::QB_Float;
					break;
				case 'date':
					return PropertyValueType::QB_Date;
					break;
				case 'bool':
					return PropertyValueType::QB_Bool;
					break;
				default:
					// str, text, xml, label
					return PropertyValueType::QB_String;
			}
		}
	}
?>
