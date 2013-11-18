<?php
	
namespace Kaigan\QBank2\API\Model;

/**
 * Represents a criteria for properties when searching.
 * @author Björn Hjortsten
 * @copyright Kaigan 2010
 * @see QBankSearchAPI
 */
class PropertyCriteria {
	const EQUAL = 'EQ';
	const NOT_EQUAL = 'NE';
	const LIKE = 'LIKE';
	const LESS = 'L';
	const LESS_OR_EQUAL = 'LE';
	const GREATER = 'G';
	const GREATER_OR_EQUAL = 'GE';
	const REGULAR_EXPRESSION = 'IN';
	const BETWEEN = 'BETWEEN';

	protected $systemName;
	protected $value;
	protected $operator;
	protected $forfetching;

	/**
	 * Creates a new PropertyCriteria
	 * @param string $systemName The system name of the property.
	 * @param mixed $value The value of the property.
	 * @param string $operator The operator of the criteria.
	 * @param bool $forfetching If set to true, inserts the property in the {@link SimpleObject}s.
	 * @author Björn Hjortsten
	 * @return PropertyCriteria
	 */
	public function __construct($systemName, $value, $operator = PropertyCriteria::EQUAL, $forfetching = false) {
		$this->systemName = $systemName;
		$this->value = $value;
		$this->operator = $operator;
		$this->forfetching = (bool)$forfetching;
	}

	/**
	 * Gets the system name of the criteria.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getSystemName() {
		return $this->systemName;
	}

	/**
	 * Gets the value of the criteria.
	 * @author Björn Hjortsten
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Gets the operator of the criteria.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getOperator() {
		return $this->operator;
	}

	/**
	 * Checks if the property should be included in the {@link SimpleObject}s.
	 * @author Björn Hjortsten
	 * @return bool
	 */
	public function isForFetching() {
		return $this->forfetching;
	}

	/**
	 * Compares this PropertyCriteria to anything else to see if it is an equal.
	 * @param mixed $var The parameter to check against.
	 * @author Björn Hjortsten
	 * @return bool True if they are equal, false if not.
	 */
	public function equals($var) {
		if (!is_a($var, 'PropertyCriteria')) {
			return false;
		}
		if ($var->getSystemName() != $this->systemName) {
			return false;
		}
		if ($var->isForFetching() !== $this->forfetching) {
			return false;
		}
		if ($var->isForFetching() === true) {
			return true;
		}
		if ($var->getOperator() != $this->operator) {
			return false;
		}
		if ($var->getValue() != $this->value) {
			return false;
		}
		return true;
	}
}
