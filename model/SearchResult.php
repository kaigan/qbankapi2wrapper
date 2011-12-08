<?php
	/**
	 * Represents a subset of results from a search. Note that a search almost never return the complete results due to paging.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @see QBankSearchAPI
	 * @package QBankAPIWrapper
	 */
	class SearchResult implements ArrayAccess, Iterator, Countable {
		
		/**
		 * The total number of results.
		 * @var int
		 */
		protected $totalResults;
		
		/**
		 * The number of results in this subset.
		 * @var int
		 */
		protected $count;
		
		/**
		 * The number of results this and previous SearchResults have returned.
		 * @var int
		 */
		protected $lastRowNumber;
		
		/**
		 * The stepping used in the search. Eg. the maximum number of results in this subset.
		 * @var int
		 */
		protected $step;
		
		/**
		 * A Unix timestamp of when the search was conducted.
		 * @var int
		 */
		protected $timeOfSearch;
		
		/**
		 * The time it took to find this subset of results.
		 * @var float
		 */
		protected $searchTime;
		
		/**
		 * A subset of the results of a search.
		 * @var array
		 */
		protected $results;
		
		/**
		 * The internal pointer for iteration of the results.
		 * @var int
		 */
		protected $position;
		
		/**
		 * Creates a new SearchResult.
		 * @param array $results A subset of the actual results of a search.
		 * @param int $totalResults The total number of search results.
		 * @param float $searchTime The time it took to find this subset of results.
		 * @param int $step The stepping used in the search. Eg. the maximum number of results in this subset.
		 * @param int $timeOfSearch A Unix timestamp of when the search was conducted.
		 * @param int $lastRowNumber The number of results this and previous SearchResults have returned.
		 * @author Björn Hjortsten
		 * @return SearchResult
		 */
		public function __construct(array $results, $totalResults, $searchTime, $step, $timeOfSearch = null, $lastRowNumber = 0) {
			$this->results = array();
			$this->count = 0;
			foreach ($results as $result) {
				if (@get_class($result) == 'SimpleObject' || @is_subclass_of($result, 'SimpleObject') === true) {
					$this->results[$this->count++] = $result;
				}
			}
			$this->searchTime = $searchTime;
			$this->totalResults = $totalResults;
			$this->step = $step;
			if (empty($timeOfSearch)) {
				$timeOfSearch = time();
			}
			$this->timeOfSearch = $timeOfSearch;
			$this->lastRowNumber = $lastRowNumber;
			$this->position = 0;
		}
		
		/**
		 * Gets the total number of results found during the search.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getTotalNumberOfResults() {
			return $this->totalResults;
		}
		
		/**
		 * Gets the number of results in this subset.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function count() {
			return $this->count;
		}
		
		/**
		 * Gets the number of results this and previous SearchResults have returned.
		 * @internal Which row in the results we are at.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getLastRowNumber() {
			return $this->lastRowNumber;
		}
		
		/**
		 * Gets the stepping used in the search. Eg. the maximum number of results in this subset.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getStepping() {
			return $this->step;
		}
		
		/**
		 * Gets the Unix timestamp of when this search was conducted.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getTimeOfSearch() {
			return $this->timeOfSearch;
		}
		
		/**
		 * Gets the time it took to find this subset of results.
		 * @author Björn Hjortsten
		 * @return int Unix timestamp.
		 */
		public function getTimeSpentSearching() {
			return $this->searchTime;
		}
		
		/**
		 * Sets a result.
		 * NOTICE: Will only accept {@link SimpleObject} or subclasses thereof.
		 * @param mixed $offset The offset in the SearchResult where the value should be set.
		 * @param mixed $value The value that should be set.
		 * @author Björn Hjortsten
		 * @see ArrayAccess::offsetSet()
		 * @return void
		 */
		public function offsetSet($offset, $value) {
			if (@get_class($value) == 'SimpleObject' || @is_subclass_of($value, 'SimpleObject') === true) {
				if (is_null($offset)) {
					$this->results[$this->count++] = $value;
				}
			}
		}
		
		/**
		 * Checks whether there is a result with the specified offset in this SearchResult.
		 * @param mixed $offset The offset in the SearchResult to check for
		 * @author Björn Hjortsten
		 * @see ArrayAccess::offsetExists()
		 * @return bool
		 */
		public function offsetExists($offset) {
			return array_key_exists($offset, $this->results);
		}
		
		/**
		 * Does not do anything! Only implemented to comply with {@link Iterator}.
		 * @param mixed $offset The offset in the SearchResult where the value should be unset.
		 * @author Björn Hjortsten
		 * @see ArrayAccess::offsetUnset()
		 * @return void
		 */
		public function offsetUnset($offset) {
			// Do nothing
		}
		
		/**
		 * Gets a result at the specified offset.
		 * @param mixed $offset The offset in the SearchResult where to get the result.
		 * @author Björn Hjortsten
		 * @see ArrayAccess::offsetGet()
		 * @return mixed {@link SimpleObject} or a sublass thereof.
		 */
		public function offsetGet($offset) {
			return $this->offsetExists($offset) ? $this->results[$offset] : null;
		}
		
		/**
		 * Rewinds the internal pointer to the first result.
		 * @author Björn Hjortsten
		 * @see Iterator::rewind()
		 * @return void
		 */
		public function rewind() {
			$this->position = 0;
		}
		
		/**
		 * Returns the current result.
		 * @author Björn Hjortsten
		 * @see Iterator::current()
		 * @return mixed {@link SimpleObject} or a sublass thereof.
		 */
		public function current() {
			return $this->results[$this->position];
		}
		
		/**
		 * Returns the current position of the internal pointer.
		 * @author Björn Hjortsten
		 * @see Iterator::key()
		 * @return int
		 */
		public function key() {
			return $this->position;
		}
		
		/**
		 * Advances the internal pointer to the next result.
		 * @author Björn Hjortsten
		 * @see Iterator::next()
		 * @return void
		 */
		public function next() {
			++$this->position;
		}
		
		/**
		 * Checks if the current position of the internal pointer is valid.
		 * @author Björn Hjortsten
		 * @see Iterator::valid()
		 * @return bool
		 */
		public function valid() {
			return array_key_exists($this->position, $this->results);
		}
		
		/**
		 * Sorts the result by the supplied comparing function.
		 * This will only sort this page, not the total result.
		 * @param callback $cmp_function The function to call when sorting.
		 * @see usort()
		 * @author Björn Hjortsten
		 * @return void
		 */
		public function sort($cmp_function) {
			usort($this->results, $cmp_function);
		}
	}
?>