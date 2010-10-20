<?php
	require_once 'PropertyValueType.php';
	
	/**
	 * Represents a QBank property.
	 * @author Björn Hjortsten
	 */
	class Property {
		protected $id;
		protected $propertyTypeId;
		protected $systemName;
		protected $title;
		protected $editable;
		protected $propertyValueType;
		protected $defaultValue;
		protected $multipleChoice;
		protected $value;
		
		/**
		 * Creates a new property.
		 * @param int $id The id of the property.
		 * @param int $propertyTypeId The id of the propertys type.
		 * @param string $systemName The system name of the property.
		 * @param string $title The display name of the property.
		 * @param mixed $value The value of the property.
		 * @param mixed $defaultValue The default value of the property.
		 * @param PropertyValueType $propertyValueType The type of the propertys value and default value.
		 * @param bool $multipleChoice If this propertys value are multiple values.
		 * @param bool $editable If the user should be able to modify the property value. {@internal Probably not used at all.}
		 * @author Björn Hjortsten
		 * @return Property
		 */
		public function __construct($id, $propertyTypeId, $systemName, $title, $value, $defaultValue = null, 
									$propertyValueType = PropertyValueType::QB_String, $multipleChoice = false, $editable = true) {
			$this->id = $id;
			$this->propertyTypeId = $propertyTypeId;
			$this->systemName = $systemName;
			$this->title = $title;
			$this->value = $value;
			$this->defaultValue = $defaultValue;
			$this->propertyValueType = $propertyValueType;
			$this->multipleChoice = $multipleChoice;
			$this->editable = $editable;
		}
		
		/**
		 * Gets the id of the property.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getId() {
			return $this->id;
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
		 * Gets the value of the property.
		 * @author Björn Hjortsten
		 * @return mixed The value of the property or NULL if there is no value. May be any type. {@link Property::getPropertyValueType()} specifies the type.
		 * @see PropertyValueType
		 */
		public function getValue() {
			return $this->value;
		}
		
		/**
		 * Gets the default value of the property.
		 * @author Björn Hjortsten
		 * @return mixed May be any type. {@link Property::getPropertyValueType()} specifies the type.
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
		 * Creates a {@link Property} from an object directly from a call to the API.
		 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
		 * @param stdClass $rawProperty The raw object from the API-call.
		 * @author Björn Hjortsten
		 * @return {@link Property}
		 */
		public static function createFromRawObject(stdClass $rawProperty) {
			$propertValueType = Property::getPropertyValueTypeFromString($rawProperty->propertyType);
			switch ($propertValueType) {
				case PropertyValueType::QB_Array:
					$value = explode('|', $rawProperty->value);
					$defaultValue = explode('|', $rawProperty->defaultValue);
					$value = array_filter($value);
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
					$value = $rawProperty->value;
					$defaultValue = $rawProperty->defaultValue;
					break;
			}
			if (empty($value)) {
				$value = null;
			}
			if (empty($defaultValue)) {
				$defaultValue = null;
			}
			return new Property(intval($rawProperty->propertyId), intval($rawProperty->id), $rawProperty->propertyName, $rawProperty->title, 
									   $value, $defaultValue, $propertValueType, (bool) $rawProperty->multiplechoice, (bool) $rawProperty->editable);
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