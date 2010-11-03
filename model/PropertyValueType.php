<?php
	/**
	 * Represents value types in a {@link Property}.
	 * @internal Actually represents an enum, not to be used as an interface.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 */
	interface PropertyValueType {
		
		/**
		 * The value is an array or a string, depends on if it is a multiple choice.
		 * @var string
		 */
		const QB_Array = 'arr';
		
		/**
		 * The value is an integer.
		 * @var string
		 */
		const QB_Int = 'int';
		
		/**
		 * The value is a floating point.
		 * @var string
		 */
		const QB_Float = 'float';
		
		/**
		 * The value is a unix timestamp.
		 * @var string
		 */
		const QB_Date = 'date';
		
		/**
		 * The value is boolean.
		 * @var string
		 */
		const QB_Bool = 'bool';
		
		/**
		 * The value is a string.
		 * @var string
		 */
		const QB_String = 'str';
	}
?>