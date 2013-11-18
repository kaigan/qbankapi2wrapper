<?php

namespace Kaigan\QBank2\API\Model;
	
/**
 * Represents the most basic form of a property. Just a name and a value.
 * NOTE: This is almost never useful, see {@link PropertyType} or {@link Property}.
 * @author Björn Hjortsten
 * @copyright Kaigan 2011
 */
class PropertyBase {

	/**
	 * The system name of the property.
	 * @var string
	 */
	protected $systemName;

	/**
	 * The value of the property.
	 * @var bool
	 */
	protected $value;

	/**
	 * Creates a new PropertyBase.
	 * @param string $systemName The system name of the property.
	 * @param mixed $value The value of the property.
	 * @author Björn Hjortsten
	 * @return PropertyBase
	 */
	public function __construct($systemName, $value) {
		$this->systemName = $systemName;
		$this->value = $value;
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
	 * Gets the value of the property.
	 * @author Björn Hjortsten
	 * @return mixed The value of the property or NULL if there is no value. May be any type. {@link PropertyType::getPropertyValueType()} specifies the type.
	 * @see PropertyValueType
	 */
	public function getValue() {
		return $this->value;
	}
}
