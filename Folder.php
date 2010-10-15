<?php
	require_once 'SimpleFolder.php';
	
	
	class Folder extends SimpleFolder {
		
		protected $children;
		
		public function __construct($name, $tree, $ownerId = 0, $created = null, $updated = null) {
			$this->children = array();
			parent::__construct($name, $tree, $ownerId, $created, $updated);
		}
		
		public function addChild(SimpleFolder $child) {
			if ($child->getParentId() == $this->getId()) {
				$this->children[] = $child;
			}
		}
		
		public function setChildren(array $children) {
			if (count($children) > 0) {
				foreach ($children as $child) {
					if (@get_class($child) == 'SimpleFolder' || @is_subclass_of($child, 'SimpleFolder')) {
						$this->addChild($child);
					}
				}
			}
		}
		
	}
?>