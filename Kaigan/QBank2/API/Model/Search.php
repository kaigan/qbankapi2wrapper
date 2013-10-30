<?php

namespace Kaigan\QBank2\API\Model;

use \InvalidArgumentException;

/**
 * Represents a search in QBank.
 * Not to be confused with {@link SearchResult} which represents the result of a Search. This is the Query.
 * @author Björn Hjortsten
 * @copyright Kaigan 2011
 */
class Search {

	/**
	 * The page of results to return.
	 * @var int
	 */
	protected $page;

	/**
	 * The number of results to return per page.
	 * @var int
	 */
	protected $pageSize;

	/**
	 * The operator of the search. True for AND search, false for OR.
	 * @var bool
	 */
	protected $andSearch;

	/**
	 * The category id to search in.
	 * @var array
	 */
	protected $categoryIds;

	/**
	 * The moodboard id to search in.
	 * @var int
	 */
	protected $moodboardId;

	/**
	 * The folder id to search in.
	 * @var int
	 */
	protected $folderId;

	/**
	 * If the folder search should be recursive.
	 * @var bool
	 */
	protected $folderRecurse;

	/**
	 * The objects to search for. An array of ints.
	 * @var array
	 */
	protected $objectIds;

	/**
	 * The freetext to search for.
	 * @var string
	 */
	protected $freeText;

	/**
	 * The title to search for.
	 * @var string
	 */
	protected $title;

	/**
	 * The {@link PropertyCriteria}s to search for.
	 * @var array
	 */
	protected $propertyCriterias;

	/**
	 * If only deployed files should be searched.
	 * @var bool
	 */
	protected $deployed;

	/**
	 * The value to sort the results by.
	 * @var string
	 */
	protected $orderBy;

	/**
	 * The direction order the sorted results are presented in.
	 * @var string
	 */
	protected $orderDirection;

	/**
	 * If {@link Object} should be returned.
	 * @var bool
	 */
	protected $advancedObjects;

	/**
	 * If children should be included as seperate objects in the search result.
	 * @var bool
	 */
	protected $includeChildren; 

	/**
	 * Include only results published to these site-template ids
	 * @var unknown_type
	 */
	protected $isPublishedTo; 

	/**
	 * An array of the folder ids to exclude from the search results.
	 * @var array
	 */
	protected $excludedFolders;

	/**
	 * An array of the object ids to exclude from the search results.
	 * @var array
	 */
	protected $excludedObjects;

	/**
	 * Creates a new Search and sets defaults.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function __construct() {
		// Set defaults...
		$this->page = 1;
		$this->pageSize = 30;
		$this->andSearch = true;
		$this->folderRecurse = false;
		$this->deployed = true;
		$this->advancedObjects = false;
		$this->setSortOrder(SearchOrder::ID_DESCENDING);
		$this->includeChildren = false;
		$this->categoryIds = array();
	}

	/**
	 * Sets which page of results to return.
	 * Default is 1.
	 * @param int $page
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setPage($page) {
		$this->page = intval($page);
	}

	/**
	 * Get the page of results to return.
	 * @author Björn Hjortsten
	 * @return int
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Sets the number of results per page.
	 * Default is 30.
	 * @param int $pageSize
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setPageSize($pageSize) {
		$this->pageSize = intval($pageSize);
	}

	/**
	 * Gets the number of results per page.
	 * @author Björn Hjortsten
	 * @return int
	 */
	public function getPageSize() {
		return $this->pageSize;
	}

	/**
	 * Sets whether it should be an AND or an OR search.
	 * Default is true.
	 * @param bool $bool
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setANDSearch($bool) {
		$this->andSearch = (bool)$bool;
	}

	/**
	 * Gets wether the search is an AND or an OR search.
	 * @author Björn Hjortsten
	 * @return bool
	 */
	public function isANDSearch() {
		return $this->andSearch;
	}

	/**
	 * Sets the id of the category to search in.
	 * @param int|array $id Either a single id or an array of ids.
	 * @throws InvalidArgumentException Thrown if $id is not numeric.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setCategoryId($id) {
		if (empty($id)) {
			return;
		}
		if (is_numeric($id)) {
			$this->categoryIds = array($id);
		} else if (is_array($id)) {
			foreach ($id as &$candidate) {
				$candidate = (int)$candidate;
			}
			unset($candidate);
			$this->categoryIds = array_filter($id);
		} else {
			throw new InvalidArgumentException('Invalid argument. Category ids must be an integer or array.');
		}
	}

	/**
	 * Gets the ids of the categories to search in.
	 * @author Björn Hjortsten
	 * @return array
	 */
	public function getCategoryId() {
		return $this->categoryIds;
	}

	/**
	 * Sets the id of the {@link Moodboard} to search in.
	 * @param int $id
	 * @throws InvalidArgumentException Thrown if $id is not numeric.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setMoodboardId($id) {
		if (!is_numeric($id)) {
			throw new InvalidArgumentException('The Moodboard id must be a number. You said "'.$id.'"');
		}
		$this->moodboardId = intval($id);
	}

	/**
	 * Gets the id of the {@link Moodboard} to search in.
	 * @author Björn Hjortsten
	 * @return int
	 */
	public function getMoodboardId() {
		return $this->moodboardId;
	}

	/**
	 * Sets the id of the {@link Folder} to search in.
	 * @param int $id
	 * @param bool $recursive Whether the folder search should be recursive.
	 * @throws InvalidArgumentException Thrown if $id is not numeric.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setFolderId($id, $recursive = false) {
		if (!is_numeric($id)) {
			throw new InvalidArgumentException('The Folder id must be a number');
		}
		$this->folderId = intval($id);
		$this->folderRecurse = (bool)$recursive;
	}

	/**
	 * Sets the ids of the {@link Folder}s to search in.
	 * @param array $folderIds An array of folder ids to search in.
	 * @param bool $recursive Whether the folder searches should be recursive.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setFolderIds(array $folderIds, $recursive = false) {
		$this->folderId = array();
		foreach ($folderIds as $folderId) {
			if (is_numeric($folderId)) {
				$this->folderId[] = (int)$folderId;
			}
		}
		$this->folderRecurse = $recursive;
	} 

	/**
	 * Gets the id of the {@link Folder} to search in.
	 * @author Björn Hjortsten
	 * @return mixed integer or array of integers.
	 */
	public function getFolderId() {
		return $this->folderId;
	}

	/**
	 * Gets whether the folder search should be recursive.
	 * @author Björn Hjortsten
	 * @return bool
	 */
	public function getFolderRecurse() {
		return $this->folderRecurse;
	}

	/**
	 * Adds an id of an {@link Object} to look for.
	 * @param int $id
	 * @throws InvalidArgumentException Thrown if $id is not numeric.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function addObjectId($id) {
		if (!is_numeric($id)) {
			throw new InvalidArgumentException('The Object id must be a number');
		}
		if (!is_array($this->objectIds)) {
			$this->objectIds = array();
		}
		$this->objectIds[] = intval($id);
		$this->objectIds = array_unique($this->objectIds, SORT_NUMERIC);
	}

	/**
	 * Adds several ids of {@link Object}s to look for.
	 * @param array $ids
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function addObjectIds(array $ids) {
		if (!is_array($this->objectIds)) {
			$this->objectIds = array();
		}
		foreach ($ids as $id) {
			if (!is_numeric($id)) {
				trigger_error('Skipping value: '.$id.'. Not a number!', E_USER_WARNING);
				continue;
			} else {
				$this->objectIds[] = intval($id);
			}
		}
		$this->objectIds = array_unique($this->objectIds, SORT_NUMERIC);
	}

	/**
	 * Gets the ids of {@link Object}s to look for.
	 * @author Björn Hjortsten
	 * @return array An array of integers.
	 */
	public function getObjectIds() {
		return $this->objectIds;
	}

	/**
	 * Sets the freetext string to search for.
	 * @param string $text
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setFreeText($text) {
		$this->freeText = strval($text);
	}

	/**
	 * Gets the freetext string to search for.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getFreeText() {
		return $this->freeText;
	}

	/**
	 * Set the title to search for.
	 * @param string $title
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = strval($title);
	}

	/**
	 * Gets the title to search for.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Adds a {@link PropertyCriteria} to the search.
	 * Note: Will replace any equal PropertyCriteria already added.
	 * @param PropertyCriteria $criteria
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function addPropertyCriteria(PropertyCriteria $criteria) {
		if (!is_array($this->propertyCriterias)) {
			$this->propertyCriterias = array();
		}
		$replaced = false;
		foreach ($this->propertyCriterias as &$crit) {
			if ($crit->equals($criteria)) {
				$crit = $criteria;
				$replaced = true;
				break;
			}
		}
		unset($crit);
		if (!$replaced) {
			$this->propertyCriterias[] = $criteria;
		}
	}

	/**
	 * Adds several {@link PropertyCriteria}s to the search.
	 * Note: Will replace any equal PropertyCriteria already added.
	 * @param array $criterias
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function addPropertyCriterias(array $criterias) {
		foreach ($criterias as $criteria) {
			if (get_class($criteria) == 'PropertyCriteria' || is_subclass_of($criteria, 'PropertyCriteria')) {
				$this->addPropertyCriteria($criteria);
			} else {
				trigger_error('Skipping value: '.$criteria.'. Not a valid PropertyCriteria! ('.get_class($criteria).')');
			}
		}
	}

	/**
	 * Removes all the {@link PropertyCriteria} to search for.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function emptyPropertyCriterias() {
		$this->propertyCriterias = null;
	}

	/**
	 * Gets all the {@link PropertyCriteria}s to search for.
	 * @author Björn Hjortsten
	 * @return array An array of PropertyCriterias.
	 */
	public function getPropertyCriterias() {
		return $this->propertyCriterias;
	}

	/**
	 * Sets whether only the deployed objects should be returned.
	 * Default is true.
	 * @param bool $bool
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setOnlyDeployed($bool) {
		$this->deployed = (bool)$bool;
	}

	/**
	 * Sets the site template ids to search for. This will trigger deployed only searching.
	 * @param array $siteTemplateIds An array of site template ids.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setIsPublishedTo(array $siteTemplateIds) {
		$this->isPublishedTo = array();
		foreach ($siteTemplateIds as $siteTemplateId) {
			if (is_numeric($siteTemplateId)) {
				$this->isPublishedTo[] = (int)$siteTemplateId;
			}
		}
		$this->deployed = true;
	}

	/**
	 * Gets the site template ids to search in.
	 * @author Björn Hjortsten
	 * @return array An array of integers.
	 */
	public function getIsPublishedTo() {
		return $this->isPublishedTo;
	}

	/**
	 * Gets whether only deployed files should be returned.
	 * @author Björn Hjortsten
	 * @return bool
	 */
	public function getOnlyDeployed() {
		return $this->deployed;
	}

	/**
	 * Sets the order the results should be delivered in.
	 * Default is {@link SearchOrder::ID_DESCENDING}.
	 * @see SearchOrder
	 * @param string $order
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setSortOrder($order) {
		list($orderBy, $orderDirection) = explode(' ', $order);
		$this->orderBy = $orderBy;
		$this->orderDirection = $orderDirection;
	}

	/**
	 * Gets which field to order by.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getOrderBy() {
		return $this->orderBy;
	}

	/**
	 * Gets the direction the results are ordered in.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getOrderDirection() {
		return $this->orderDirection;
	}

	/**
	 * Sets whether to return advanced objects.
	 * Default is false.
	 * @param bool $bool
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setAdvancedObjects($bool) {
		$this->advancedObjects = (bool)$bool;
	}

	/**
	 * Gets whether the search should return advanced objects
	 * @author Björn Hjortsten
	 * @return bool
	 */
	public function getAdvancedObjects() {
		return $this->advancedObjects;
	}

	/**
	 * Sets whether to include child {@link SimpleObject}s as seperate entitys in the result.
	 * @param bool $bool
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setIncludeChildren($bool) {
		$this->includeChildren = (bool)$bool;
	}

	/**
	 * Gets whether to include child {@link SimpleObject}s as seperate entitys in the result.
	 * @author Björn Hjortsten
	 * @return bool
	 */
	public function getIncludeChildren() {
		return $this->includeChildren;
	}

	/**
	 * Sets the folders which content to exclude from the search.
	 * @author Björn Hjortsten
	 * @param array $folderIds An array of {@link Folder} ids.
	 */
	public function setExcludedFolders(array $folderIds) {
		$this->excludedFolders = $folderIds;
	}

	/**
	 * Gets the folders which content to exclude from the search.
	 * @author Björn Hjortsten
	 * @return array An array of {@link Folder} ids.
	 */
	public function getExcludedFolders() {
		return $this->excludedFolders;
	}

	/**
	* Sets the objects which to exclude from the search.
	* @author Björn Hjortsten
	* @param array $objectIds An array of {@link Object} ids.
	*/
	public function setExcludedObjects(array $objectIds) {
		$this->excludedObjects = $objectIds;
	}

	/**
	 * Gets the objects which to exclude from the search.
	 * @author Björn Hjortsten
	 * @return array An array of {@link Object} ids.
	 */
	public function getExcludedObjects() {
		return $this->excludedObjects;
	}
}
