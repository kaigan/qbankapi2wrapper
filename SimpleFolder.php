<?php
	
	/**
	 * Represents a QBank folder with the most basic information.
	 * NOTE: Does not present any info about parents, children or content.
	 * @internal This class does actually contain information about parents, but it does not present it publically.
	 * @author Björn Hjortsten
	 */
	class SimpleFolder {
		
		/**
		 * @var int The id of the folder.
		 */
		protected $id;
		
		/**
		 * @var string The name of the folder.
		 */
		protected $name;
		
		/**
		 * @var string The folders place in the folder-tree. Expressed in a hyphen-delimited hierarkial list of ids.
		 */
		protected $tree;
		
		/**
		 * @var int The owner of the folder's user id.
		 */
		protected $ownerId;
		
		/**
		 * @var int Unix timestamp specifying when the folder was created.
		 */
		protected $created;
		
		/**
		 * @var int Unix timestamp specifying when the folder was last updated.
		 */
		protected $updated;
		
		/**
		 * @var int This folders direct parent folder id.
		 */
		protected $parentFolderId;
		
		/**
		 * Creates a new {@link SimpleFolder}.
		 * @param string $name The name of the folder.
		 * @param string $tree The folders place in the folder-tree. Expressed in a hyphen-delimited hierarkial list of ids.
		 * @param int $ownerId The owner of the folder's user id.
		 * @param int $created Unix timestamp specifying when the folder was created.
		 * @param int $updated Unix timestamp specifying when the folder was last updated.
		 * @author Björn Hjortsten
		 * @return {@link SimpleFolder}
		 */
		public function __construct($name, $tree, $ownerId = 0, $created = null, $updated = null) {
			$this->name = $name;
			$this->tree = $tree;
			$this->ownerId = $ownerId;
			$this->created = $created;
			$this->updated = $updated;
			$this->id = $this->getLastIdFromTree($this->tree);
			$this->parentFolderId = $this->calculateParentId();
		}
		
		/**
		 * Gets the folders id.
		 * @author Björn Hjortsten
		 * @return int The folders id.
		 */
		public function getId() {
			return $this->id;
		}
		
		/**
		 * Gets the folders name.
		 * @author Björn Hjortsten
		 * @return string The folders name.
		 */
		public function getName() {
			return $this->name;
		}
		
		/**
		 * Gets the folders tree.
		 * @author Björn Hjortsten
		 * @return string The folders tree.
		 */
		protected function getTree() {
			return $this->tree;
		}
		
		/**
		 * Gets the user id of the owner of the folder.
		 * @author Björn Hjortsten
		 * @return int The user id of the owner of the folder.
		 */
		public function getOwnerId() {
			return $this->ownerId;
		}
		
		/**
		 * Gets the Unix timestamp specifying when the folder was created.
		 * @author Björn Hjortsten
		 * @return int Unix timestamp specifying when the folder was created.
		 */
		public function getCreated() {
			return $this->created;
		}
		
		/**
		 * Gets the Unix timestamp specifying when the folder last was updated.
		 * @author Björn Hjortsten
		 * @return int Unix timestamp specifying when the folder last was updated.
		 */
		public function getUpdated() {
			return $this->updated;
		}
		
		/**
		 * Gets the folders parent id.
		 * @author Björn Hjortsten
		 * @return int The folders parent id.
		 */
		protected function getParentId() {
			return $this->parentFolderId;
		}
		
		/**
		 * Gets the last id from a tree string.
		 * @param string $tree The tree to be processed.
		 * @author Björn Hjortsten
		 * @return int The last id of the tree.
		 */
		protected function getLastIdFromTree($tree) {
			$tree = explode('-', $tree);
			$id = array_pop($tree);
			if ($id === null) {
				$id = 0;
			} else {
				$id = intval($id, 16);
			}
			return $id;
		}
		
		/**
		 * Gets the folders parent folder id.
		 * @author Björn Hjortsten
		 * @return int The parents folder id.
		 */
		protected function calculateParentId() {
			$tree = explode('-', $this->getTree());
			array_pop($tree);
			$tree = implode('-', $tree);
			return $this->getLastIdFromTree($tree);
		}
		
		/**
		 * Compares two folders by their place in the folder tree.
		 * @param SimpleFolder $a The first folder.
		 * @param SimpleFolder $b The second folder.
		 * @author Björn Hjortsten
		 * @return int Returns < 0 if $a is less than $b; > 0 if $a  is greater than $b, and 0 if they are equal. 
		 */
		protected static function compareByTree(SimpleFolder $a, SimpleFolder $b) {
			$treeA = explode('-', $a->getTree());
			$treeB = explode('-', $b->getTree());
			for ($i = 0; $i < count($treeA) && $i < count($treeB); $i++) {
				// If the node value is equal...
				if (intval($treeA[$i], 16) == intval($treeB[$i], 16)) {
					// ...and at the end of either node tree, compare tree length...
					if ($i == (count($treeA) - 1) || $i == (count($treeB) - 1)) {
						// ...If they are equal, return 0
						if (count($treeA) == count($treeB)) {
							return 0;
						}
						// ...If not equal, shortest tree wins
						return (count($treeA) < count($treeB)) ? -1 : 1;
					}
					// ...and not at the end of either tree, continue
					continue;
				}
				// If the node value is not equal, compare values 
				return (intval($treeA[$i], 16) < intval($treeB[$i], 16)) ? -1 : 1;
			}
		}
	}
?>