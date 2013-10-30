<?php

namespace Kaigan\QBank2\API\Model;

/**
 * Represents a request for properties when searching.
 * @author Björn Hjortsten
 * @copyright Kaigan 2011
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
