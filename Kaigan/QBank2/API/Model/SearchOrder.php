<?php

namespace Kaigan\QBank2\API\Model;

/**
 * Defines the possible orders a search can have.
 * @author Björn Hjortsten
 * @copyright Kaigan 2010
 */
interface SearchOrder {
	const ID_DESCENDING = 'created DESC';
	const ID_ASCENDING = 'created ASC';
	const NAME_DESCENDING = 'name DESC';
	const NAME_ASCENDING = 'name ASC';
	const LAST_MODIFIED_DESCENDING = 'modified DESC';
	const LAST_MODIFIED_ASCENDING = 'modified ASC';
	const FOLDER_ORDER_DESCENDING = 'folderorder DESC';
	const FOLDER_ORDER_ASCENDING = 'folderorder ASC';
	const FILENAME_ASCENDING = 'filename ASC';
	const FILENAME_DESCENDING = 'filename DESC';
}
