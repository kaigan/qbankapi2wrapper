<?php
	/**
	 * Defines the possible orders a search can have.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK AB 2010
	 * @package QBankAPIWrapper
	 */
	interface SearchOrder {
		const ID_DESCENDING = 'created DESC';
		const ID_ASCENDING = 'created ASC';
		const NAME_DESCENDING = 'name DESC';
		const NAME_ASCENDING = 'name ASC';
		const LAST_MODIFIED_DESCENDING = 'modified DESC';
		const LAST_MODIFIED_ASCENDING = 'modified ASC';
	}
?>