<?php
	require_once 'QBankAPI.php';
	
	require_once 'model/SearchOrder.php';
	require_once 'model/SearchResult.php';
	require_once 'model/Search.php';
	require_once 'model/PropertyCriteria.php';
	require_once 'model/PropertyRequest.php';
	require_once 'model/SimpleObject.php';
	require_once 'model/Object.php';
	
	/**
	 * Provides functionality for searching in QBank.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @see SearchResult
	 * @see PropertyCriteria
	 * @package QBankAPIWrapper
	 */
	class QBankSearchAPI extends QBankAPI {
		
		/**
		 * Searches in QBank.
		 * @param string $freetext A text string to search for.
		 * @param int $folderId The id of the folder to look in.
		 * @param int $categoryId The id of the category to look in.
		 * @param array $objectIds An array containing the ids of objects to look for.
		 * @param array $properties An array containing the {@link PropertyCriteria}ias to look for.
		 * @param int $page The page of results to return.
		 * @param int $pageSize The number of results to return per page.
		 * @param string $sort How to order the results, see {@link SearchOrder}.
		 * @param bool $deployed If true, will only get deployed media.
		 * @param bool $advanced If true, will get {@link Object}s instead of {@link SimpleObject}s.
		 * @param bool $exclusive If true, will treat $properties as requirements. If false, will treat properties as optional.
		 * @param bool $folderRecurse If true, will search the supplied folders subfolders as well. If false, will only search in the supplied folder.
		 * @author Björn Hjortsten
		 * @return SearchResult
		 */
		public function search($freetext = null, $folderId = null, $categoryId = null, array $objectIds = null, array $properties = null,
							   $page = 1, $pageSize = 30, $sort = SearchOrder::ID_DESCENDING, $deployed = true, $advanced = false, $exclusive = true,
							   $folderRecurse = false) {
			$data = array();
			$data['page'] = $page;
			$data['pageSize'] = $pageSize;
			list($orderBy, $orderDirection) = explode(' ', $sort);
			$data['orderBy'] = $orderBy;
			$data['orderDirection'] = $orderDirection;
			if (isset($freetext)) {
				$data['freetext'] = $freetext;
			}
			if (isset($folderId)) {
				$data['folderId'] = $folderId;
				$data['recursive'] = $folderRecurse;
			}
			if (isset($categoryId)) {
				$data['categoryId'] = $categoryId;
			}
			if (isset($objectIds) && is_array($objectIds)) {
				$objectIds = array_unique($objectIds, SORT_NUMERIC);
				$data['objectIds'] = implode(',', $objectIds);
			}
			if (isset($properties) && is_array($properties)) {
				foreach ($properties as $property) {
					if (@get_class($property) == 'PropertyCriteria' && $property->getSystemName() != 'system_media_status') {
						$data['properties'][] = array('name' => $property->getSystemName(), 'value' => $property->getValue(),
													  'operator' => $property->getOperator(), 'forfetching' => $property->isForFetching());
					}
				}
			}
			
			if ($deployed === true) {
				$data['properties'][] = array('name' => 'system_media_status', 'value' => 'Published', 'operator' => PropertyCriteria::EQUAL, 'forfetching' => false);
			}
			if ($exclusive !== true) {
				$data['operator'] = 'OR';
			} else {
				$data['operator'] = 'AND';
			}
			$result = $this->call('search', $data, true);
			if (is_array($result->data->searchResults) && !empty($result->data->searchResults)) {
				foreach ($result->data->searchResults as $rawObject) {
					$objects[] = SimpleObject::createFromRawObject($rawObject);
				}
				if ($advanced === true) {
					foreach ($objects as $key => $object) {
						$calls[] = array('name' => $key, 'function' => 'getobjectinformation', 'arguments' => array('objectId' => $object->getId()));
					}
					$result2 = $this->call('batch', array('calls' => $calls));
					$objects = array();
					foreach ($result2->results as $res) {
						$objects[] = Object::createFromRawObject($res->data);
					}
				}
			} else {
				$objects = array();
			}
			$searchResult = new SearchResult($objects, intval($result->data->counter), $result->data->timeSearching, $result->data->step, $result->data->timeStamp, $result->data->end);
			return $searchResult;
		}
		
		/**
		 * Executes a search in QBank.
		 * @param Search $search
		 * @author Björn Hjortsten
		 * @return SearchResult
		 */
		public function execute(Search $search) {
			$volatile = false;
			$data = array();
			$data['page'] = $search->getPage();
			$data['pageSize'] = $search->getPageSize();
			$data['orderBy'] = $search->getOrderBy();
			$data['orderDirection'] = $search->getOrderDirection();
			if ($search->isANDSearch()) {
				$data['operator'] = 'AND';
			} else {
				$data['operator'] = 'OR';
			}
			if (!is_null($search->getCategoryId()) && $search->getCategoryId() > 0) {
				$data['categoryId'] = $search->getCategoryId();
			}
			if (!is_null($search->getFreeText()) && $search->getFreeText()) {
				$data['freetext'] = $search->getFreeText();
			}
			if (!is_null($search->getTitle()) && $search->getTitle()) {
				$data['title'] = $search->getTitle();
			}
			if (!is_null($search->getFolderId()) && $search->getFolderId() > 0) {
				$data['folderId'] = $search->getFolderId();
				$data['recursive'] = $search->getFolderRecurse();
				if ($volatile) {
					// Volatile since folders and moodboards collide
					trigger_error('Possible volatile search! You may not get the results you expect.', E_USER_WARNING);
				}
				$volatile = true;
			}
			if (!is_null($search->getMoodboardId()) && $search->getMoodboardId() > 0) {
				$data['moodboardId'] = $search->getMoodboardId();
				if ($volatile) {
					// Volatile since folders and moodboards collide
					trigger_error('Possible volatile search! You may not get the results you expect.', E_USER_WARNING);
				}
				$volatile = true;
			}
			if (is_array($search->getObjectIds()) && $search->getObjectIds()) {
				$data['objectIds'] = implode(',', $search->getObjectIds());
			}
			if (is_array($search->getPropertyCriterias()) && $search->getPropertyCriterias()) {
				foreach ($search->getPropertyCriterias() as $criteria) {
					if (!($criteria->getSystemName() == 'system_media_status' && $criteria->isForFetching() == false)) {
						$data['properties'][] = array(
							'name' 			=> $criteria->getSystemName(),
							'value'			=> $criteria->getValue(),
							'operator'		=> $criteria->getOperator(),
							'forfetching'	=> $criteria->isForFetching()
						);
					}
				}
			}
			if ($search->getOnlyDeployed()) {
				$data['properties'][] = array(
					'name'			=> 'system_media_status',
					'value'			=> 'Published',
					'operator'		=> PropertyCriteria::EQUAL,
					'forfetching'	=> false
				);
				
				$publishedTo = $search->getIsPublishedTo();
				if (!empty($publishedTo)) {
					$data['publishedTo'] = $publishedTo;
				}
			}
			if ($search->getIncludeChildren() == true) {
				$data['includeChildren'] = true;
			}
			if (is_array($search->getExcludedFolders()) && $search->getExcludedFolders()) {
				$data['excludedFolderIds'] = $search->getExcludedFolders();
			}
			if (is_array($search->getExcludedObjects()) && $search->getExcludedObjects()) {
				$data['excludedObjectIds'] = $search->getExcludedObjects();
			}
			
			return $this->processResult($this->call('searchfrontend', $data, true), $search->getAdvancedObjects());
		}
		
		/**
		 * Processes the raw results from a QBank search and creates a {@link SearchResult}.
		 * @param stdClass $result
		 * @param bool $advanced Whether to populate the SearchResult with Advanced objects.
		 * @author Björn Hjortsten
		 * @return SearchResult
		 */
		protected function processResult($result, $advanced = false) {
			if (is_array($result->data->searchResults) && !empty($result->data->searchResults)) {
				foreach ($result->data->searchResults as $rawObject) {
					$objects[] = SimpleObject::createFromRawObject($rawObject);
				}
				if ($advanced === true) {
					foreach ($objects as $key => $object) {
						$calls[] = array('name' => $key, 'function' => 'getobjectinformation', 'arguments' => array('objectId' => $object->getId()));
					}
					$result2 = $this->call('batch', array('calls' => $calls), true);
					$objects = array();
					foreach ($result2->results as $res) {
						$objects[] = Object::createFromRawObject($res->data);
					}
				}
			} else {
				$objects = array();
			}
			$searchResult = new SearchResult($objects, intval($result->data->counter), $result->data->timeSearching, $result->data->step, $result->data->timeStamp, $result->data->end);
			return $searchResult;
		}
	}
?>
