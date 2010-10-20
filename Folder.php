<?php
	require_once 'SimpleFolder.php';
	require_once 'exceptions/FolderException.php';
	
	/**
	 * Represents a QBank folder.
	 * @author Björn Hjortsten
	 */
	class Folder extends SimpleFolder {
		
		/**
		 * An array of {@link Folder}s. This folders subfolders.
		 * @var array
		 */
		protected $children;
		
		/**
		 * An array of {@link Property}(ies). This folders properties.
		 * @var array
		 */
		protected $properties;
		
		/**
		 * Creates a new {@link Folder}.
		 * @param string $name The name of the folder.
		 * @param string $tree The folders place in the folder-tree. Expressed in a hyphen-delimited hierarkial list of ids.
		 * @param int $ownerId The owner of the folder's user id.
		 * @param int $created Unix timestamp specifying when the folder was created.
		 * @param int $updated Unix timestamp specifying when the folder was last updated.
		 * @author Björn Hjortsten
		 * @return {@link Folder}
		 */
		public function __construct($name, $tree, $ownerId = 0, $created = null, $updated = null, $properties = array()) {
			$this->children = array();
			$this->setProperties($properties);
			parent::__construct($name, $tree, $ownerId, $created, $updated);
		}
		
		/**
		 * Adds a subfolder to this folder.
		 * @param Folder $child The folder to add as subfolder.
		 * @throws FolderException Thrown if this folder is not the parent of the child folder (DNA-tested).
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected function addChild(Folder $child) {
			if ($child->getParentId() == $this->getId()) {
				$this->children[$child->getId()] = $child;
				
			} else {
				throw new FolderException(sprintf('The supplied folder is not a child of this folder. This folders id: %d, childs id: %d, childs parent id: %d',
												   $this->getId(), $child->getId(), $child->getParentId()));
			}
		}
		
		/**
		 * Adds several subfolders to this folder.
		 * @param array $children An array of {@link Folder}s to be added as subfolders.
		 * @throws FolderException Thrown if this folder is not the parent of the child folder (DNA-tested).
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected function addChildren(array $children) {
			foreach ($children as $child) {
				if (@get_class($child) == 'Folder') {
					$this->addChild($child);
				}
			}
		}
		
		/**
		 * Sets all subfolders to this folder.
		 * @internal Empties the array of children first, then adds new subfolders.
		 * @param array $children An array of {@link Folder}s to be added as subfolders.
		 * @throws FolderException Thrown if this folder is not the parent of the child folder (DNA-tested).
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected function setChildren(array $children) {
			$this->children = array();
			$this->addChildren($children);
		}
		
		/**
		 * Gets all subfolders to this {@link Folder}.
		 * @author Björn Hjortsten
		 * @return array An array of {@link Folder}s.
		 */
		public function getChildren() {
			return $this->children;
		}
		
		/**
		 * Gets a specific subfolder.
		 * @param int $id The id of the subfolder to get.
		 * @throws FolderException Thrown if there is no subfolder with the specified id.
		 * @author Björn Hjortsten
		 * @return Folder 
		 */
		public function getChild($id) {
			if (!isset($this->children[$id])) {
				throw new FolderException(sprintf('There is no child with id %d.', $id));
			}
			return $this->children[$id];
		}
		
		/**
		 * Adds a property to the folder.
		 * @param Property $property The property to add.
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected function addProperty(Property $property) {
			$this->properties[$property->getSystemName()] = $property;
		}
		
		/**
		 * Adds several properties to the folder.
		 * @param array $properties An array of {@link Property}(ies) to be added as subfolder.
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected function addProperties(array $properties) {
			foreach ($properties as $property) {
				if (@get_class($property) == 'Property') {
					$this->addProperty($property);
				}
			}
		}
		
		/**
		 * Sets all the properties of the folder.
		 * @param array $properties An array of {@link Property}(ies) to be added as subfolder.
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected function setProperties(array $properties) {
			$this->properties = array();
			$this->addProperties($properties);
		}
		
		/**
		 * Gets all the properties of this folder.
		 * @author Björn Hjortsten
		 * @return array An array of {@link Property}(ies).
		 */
		public function getProperties() {
			return $this->properties;
		}
		
		/**
		 * Gets a {@link Property} of the {@link Folder}.
		 * @internal Geting via id is slower.
		 * @param mixed $identifier Either the system name of the property or the propertys id.
		 * @throws PropertyException Thrown if the folder does not have a property with the specified identifier.
		 * @author Björn Hjortsten
		 * @return Property
		 */
		public function getProperty($identifier) {
			if (is_numeric($identifier)) {
				foreach ($this->properties as $property) {
					if ($property->getId() == $identifier) {
						return $property;
					}
				}
				throw new PropertyException(sprintf('No such property with system name %d', $identifier));
			} else {
				if (!isset($this->properties[$identifier])) {
					throw new PropertyException(sprintf('No such property with system name %s.', $identifier));
				}
				return $this->properties[$identifier];
			}
		}
		
		/**
		 * Creates a tree of a flat array of {@link Folder}s or {@link SimpleFolder}s.
		 * @param array $folders An array of {@link Folder}s or {@link SimpleFolder}s. May be mixed.
		 * @throws FolderException Thrown if an error occurs while building the folder tree.
		 * @author Björn Hjortsten
		 * @return array An array of top level {@link Folder}s.
		 */
		public static function createTree(array $folders) {
			usort($folders, array('SimpleFolder', 'compareByTree'));
			$tree = array();
			$shortestTree = Folder::getShortestTree($folders);
			foreach ($folders as $folder) {
				if (@get_class($folder) == 'SimpleFolder') {
					$folder = Folder::createFromSimpleFolder($folder);
				}
				try {
					Folder::addToTree($tree, $folder, $shortestTree);
				} catch (FolderException $fe) {
					throw new FolderException(sprintf('Failed to generate tree: %s'), $fe->getMessage());
				}
			}
			return $tree;
		}
		
		/**
		 * Creates a {@link Folder} of a {@link SimpleFolder}.
		 * @param SimpleFolder $folder The {@link SimpleFolder} to convert.
		 * @author Björn Hjortsten
		 * @return Folder
		 */
		protected static function createFromSimpleFolder(SimpleFolder $folder) {
			return new Folder($folder->getName(), $folder->getTree(), $folder->getOwnerId(), $folder->getCreated(), $folder->getUpdated());
		}
		
		/**
		 * Adds a folder to its place in a folder tree.
		 * @internal Its crucial that all parent folders are already in the tree or this will fail.
		 * @param array $tree The array of top level folders to add to. {@internal Passed by value.}
		 * @param Folder $folder The folder to add.
		 * @param string $shortestTree The shortest tree that in the hierarchy.
		 * @throws FolderException Thrown if the $tree is not prepared to recieve the $folder.
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected static function addToTree(array &$tree, Folder $folder, $shortestTree) {
			$rootLevel = count(array_filter(explode('-', $shortestTree)));
			$folderTree = explode('-', $folder->getTree());
			$folderTree = array_filter($folderTree);
			$folderTree = Folder::arrangeKeys($folderTree);
			if (count($folderTree) == $rootLevel) {
				$tree[$folder->getId()] = $folder;
			} else {
				try {
					$currentFolder = $tree[intval($folderTree[0], 16)];
					for ($i = $rootLevel; $i < count($folderTree) - 1; $i++) {
						$currentFolder = $currentFolder->getChild(intval($folderTree[$i], 16));
					}
					$currentFolder->addChild($folder);
				} catch (FolderException $fe) {
					throw new FolderException(sprintf('Error while adding folder to tree. Current folder id: %d, child folder id: %d', $currentFolder->getId(), $folder->getId()));
				}
			}
		}
		
		/**
		 * Rearranges the keys in an array to be strictly numerical with no gaps starting from 0.
		 * @param array $array The array to be rearranged.
		 * @author Björn Hjortsten
		 * @return array
		 */
		protected static function arrangeKeys(array $array) {
			$i = 0;
			$out = array();
			if (is_array($array)) {
				foreach ($array as $key => $value) {
					$out[$i] = $value;
					$i++;
				}
			}
			return $out;
		}
		
		/**
		 * Gets the shortest tree from an array of folders.
		 * @param array $folders The folders to look in.
		 * @author Björn Hjortsten
		 * @return string The shortest tree.
		 */
		protected static function getShortestTree(array $folders) {
			$depth = null;
			$tree;
			foreach ($folders as $folder) {
				if (@get_class($folder) == 'Folder') {
					$currentTree = explode('-', $folder->getTree());
					$currentTree = array_filter($currentTree);
					if ($depth == null) {
						$depth = count($currentTree);
						$tree = $folder->getTree();
					} else if (count($currentTree) < $depth) {
						$depth = count($currentTree);
						$tree = $folder->getTree();
					}
				}
			}
			return $tree;
		}
	}
?>