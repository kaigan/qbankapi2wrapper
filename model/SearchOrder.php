<?php
	interface SearchOrder {
		const ID_DESCENDING = 'created DESC';
		const ID_ASCENDING = 'created ASC';
		const NAME_DESCENDING = 'name DESC';
		const NAME_ASCENDING = 'name ASC';
		const LAST_MODIFIED_DESCENDING = 'modified DESC';
		const LAST_MODIFIED_ASCENDING = 'modified ASC';
	}
?>