<?php

namespace Kaigan\QBank2\API\Model;

use \stdClass;

/**
 * Represents a QBank property.
 * @author Björn Hjortsten
 * @copyright Kaigan 2010
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
		parent::__construct($propertyTypeId, $systemName, $title, $value, $defaultValue, $propertyValueType, $multipleChoice, $editable);
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
		if (@!is_null($rawProperty->propertyType)) {
			$propertyValueType = Property::getPropertyValueTypeFromString($rawProperty->propertyType);
		} else {
			$propertyValueType = null;
		}

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
			case PropertyValueType::QB_String:
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
			default:
				return Property::createFromSecondaryRawObject($rawProperty);
				break;
		}
		$property = new Property(intval($rawProperty->propertyId), intval($rawProperty->id), $rawProperty->propertyName, $rawProperty->title, 
								   $value, $defaultValue, $propertyValueType, (bool) $rawProperty->multiplechoice, (bool) $rawProperty->editable);
		@$property->qbankValueType = $rawProperty->propertyType;
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
	 * Creates a {@link Property} from an object directly from a call to the API.
	 * Guesses the property since much less information is available.
	 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
	 * @param stdClass $rawProperty The raw object from the API-call.
	 * @author Björn Hjortsten
	 * @return Property
	 */
	protected static function createFromSecondaryRawObject(stdClass $rawProperty) {
		if (!empty($rawProperty->arrValue)) {
			$value = explode('|', $rawProperty->arrValue);
			$value = array_filter($value);
			array_walk($value, array('Property', 'utrim'));
			$propertyValueType = PropertyValueType::QB_Array;
		} elseif (!is_null($rawProperty->boolValue)) {
			$value = (bool) $rawProperty->boolValue;
			$propertyValueType = PropertyValueType::QB_Bool;
		} elseif (!empty($rawProperty->dateValue)) {
			$value = strtotime($rawProperty->dateValue);
			$propertyValueType = PropertyValueType::QB_Date;
		} elseif (!is_null($rawProperty->floatValue)) {
			$value = floatval($rawProperty->floatValue);
			$propertyValueType = PropertyValueType::QB_Float;
		} elseif (!empty($rawProperty->labelValue)) {
			$value = strval($rawProperty->labelValue);
			$propertyValueType = PropertyValueType::QB_String;
		} elseif (!is_null($rawProperty->intValue)) {
			$value = intval($rawProperty->intValue);
			$propertyValueType = PropertyValueType::QB_Int;
		} elseif (!empty($rawProperty->textValue)) {
			$value = strval($rawProperty->textValue);
			$propertyValueType = PropertyValueType::QB_String;
		} elseif (!empty($rawProperty->xmlValue)) {
			$value = strval($rawProperty->xmlValue);
			$propertyValueType = PropertyValueType::QB_String;
		} else {
			$value = $rawProperty->strValue;
			$propertyValueType = PropertyValueType::QB_String;
		}
		$property = new Property(intval($rawProperty->id), intval($rawProperty->propertyTypeId), $rawProperty->propertyName, $rawProperty->title,
								 $value, null, $propertyValueType, false, false);
		$property->qbankValueType = null;
		$property->editable = null;
		$property->mandatory = null;
		$property->keywords = null;
		$property->link = null;
		$property->info = null;

		return $property;
	}

	private static function utrim(&$value) {
		$value = trim($value);
	}
}

