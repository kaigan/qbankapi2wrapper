<?php
	require_once 'model/PropertyValueType.php';
	require_once 'model/PropertyType.php';
	
	/**
	 * Represents a QBank property.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 */
	class Property extends PropertyType {
		
		/**
		 * The id of the property.
		 * @var int
		 */
		protected $id;
		
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
		 * @param bool $editable If the user should be able to modify the property value. {@internal Probably not used at all. }
		 * @author Björn Hjortsten
		 * @return Property
		 */
		public function __construct($id, $propertyTypeId, $systemName, $title, $value, $defaultValue = null, 
									$propertyValueType = PropertyValueType::QB_String, $multipleChoice = false, $editable = true) {
			$this->id = $id;
			parent::__construct($propertyTypeId, $systemName, $title, $value, $propertyValueType, $multipleChoice, $editable);
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
		 * Creates a {@link Property} from an object directly from a call to the API.
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
			$property = new Property(intval($rawProperty->propertyId), intval($rawProperty->id), $rawProperty->propertyName, $rawProperty->title, 
									   $value, $defaultValue, $propertyValueType, (bool) $rawProperty->multiplechoice, (bool) $rawProperty->editable);
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
	}
?>
