<?php
	require_once 'QBankAPI.php';
	
	require_once 'model/SearchOrder.php';
	require_once 'model/SearchResult.php';
	require_once 'model/PropertyCriteria.php';
	
	/**
	 * Provides functionality for searching in QBank.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @see SearchResult
	 * @see PropertyCriteria
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
		 * @author Björn Hjortsten
		 * @return SearchResult
		 */
		public function search($freetext = null, $folderId = null, $categoryId = null, array $objectIds = null, array $properties = null,
							   $page = 1, $pageSize = 30, $sort = SearchOrder::ID_DESCENDING, $deployed = true, $advanced = false, $exclusive = true) {
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
			}
			if (isset($categoryId)) {
				$data['categoryId'] = $categoryId;
			}
			if (isset($objectIds) && is_array($objectIds)) {
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
			//FIXME AND returnerar alltid 0 resultat
			/*
			if ($exclusive !== true) {
				$data['operator'] = 'OR';
			} else {
				$data['operator'] = 'AND';
			}
			*/
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