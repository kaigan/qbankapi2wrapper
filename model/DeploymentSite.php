<?php
	/**
	 * Represents deployment information.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK AB 2011
	 * @package QBankAPIWrapper
	 */
	class DeploymentSite {
		
		/**
		 * The id of the deployment site
		 * @var int
		 */
		protected $id;
		
		/**
		 * The name of the deployment site
		 * @var string
		 */
		protected $name;
		
		/**
		 * The url to the deployed original file.
		 * @var string
		 */
		protected $original;
		
		/**
		 * The url to the deployed medium file.
		 * @var string
		 */
		protected $medium;
		
		/**
		 * The url to the deployed thumbnail file.
		 * @var string
		 */
		protected $thumbnail;
		
		/**
		 * The deployed pdf-pages.
		 * @var array
		 */
		protected $pdfPages;
		
		/**
		 * The deployed database of text for a pdf.
		 * @var string
		 */
		protected $pdfDatabase;
		
		/**
		 * Creates a new DeploymentSite.
		 * @param int $id The id of the deployment site.
		 * @param string The name of the deployment site.
		 * @author Björn Hjortsten
		 * @return DeploymentSite
		 */
		public function __construct($id, $name) {
			$this->id = $id;
			$this->name = $name;
			$this->pdfPages = array();
		}
		
		/**
		 * Gets the deployment site id.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getId() {
			return $this->id;
		}
		
		/**
		 * Gets the deployment site's name.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getSiteName() {
			return $this->name;
		}
		
		/**
		 * Gets the url to the deployed original file.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getOriginal() {
			return $this->original;
		}
		
		/**
		 * Gets the url to the deployed medium file.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getMedium() {
			return $this->medium;
		}
		
		/**
		 * Gets the url to the deployed thumbnail file.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getThumbnail() {
			return $this->thumbnail;
		}
		
		/**
		 * Gets the urls to the deployed pdf pages.
		 * @author Björn Hjortsten
		 * @return array An array of strings.
		 */
		public function getPdfPages() {
			return $this->pdfPages;
		}
		
		/**
		 * Gets the url to the deployed text database for a pdf.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getPdfDatabase() {
			return $this->pdfDatabase;
		}
		
		/**
		 * Creates an object from the raw object of a call to the API.
		 * @param stdClass $rawObject
		 * @author Björn Hjortsten
		 * @return DeploymentSite
		 */
		public static function createFromRawObject($rawObject) {
			$siteInfo = new DeploymentSite(intval($rawObject->siteId), strval($rawObject->name));
			$siteInfo->original = strval($rawObject->original);
			$siteInfo->medium = strval($rawObject->medium);
			$siteInfo->thumbnail = strval($rawObject->thumbnail);
			foreach ($rawObject->pages as $page) {
				$siteInfo->pdfPages[] = strval($page);
			}
			if (@$rawObject->database) {
				$siteInfo->pdfDatabase = $rawObject->database;
			}
			return $siteInfo;
		}
	}
?>