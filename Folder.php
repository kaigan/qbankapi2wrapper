<?php
	require_once 'SimpleFolder.php';
	require_once 'exceptions/FolderException.php';
	
	/**
	 * Represents a QBank folder.
	 * @author Björn Hjortsten
	 */
	class Folder extends SimpleFolder {
		
		/**
		 * @var array An array of {@link Folder}s. This folders subfolders.
		 */
		protected $children;
		
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
		public function __construct($name, $tree, $ownerId = 0, $created = null, $updated = null) {
			$this->children = array();
			parent::__construct($name, $tree, $ownerId, $created, $updated);
		}
		
		/**
		 * Adds a subfolder to this folder.
		 * @param Folder $child The folder to add as subfolder.
		 * @throws FolderException Thrown if this folder is not the parent of the child folder (DNA-tested).
		 * @author Björn Hjortsten
		 * @return void
		 */
		public function addChild(Folder $child) {
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
		public function addChildren(array $children) {
			if (count($children) > 0) {
				foreach ($children as $child) {
					if (@get_class($child) == 'Folder') {
						$this->addChild($child);
					}
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
		 * Creates a tree of a flat array of {@link Folder}s or {@link SimpleFolder}s.
		 * @param array $folders An array of {@link Folder}s or {@link SimpleFolder}s. May be mixed.
		 * @throws FolderException Thrown if an error occurs while building the folder tree.
		 * @author Björn Hjortsten
		 * @return array An array of top level {@link Folder}s.
		 */
		public static function createTree(array $folders) {
			usort($folders, array('SimpleFolder', 'compareByTree'));
			$tree = array();
			foreach ($folders as $folder) {
				if (@get_class($folder) == 'SimpleFolder') {
					$folder = Folder::createFromSimpleFolder($folder);
				}
				try {
					Folder::addToTree($tree, $folder);
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
		 * @throws FolderException Thrown if the $tree is not prepared to recieve the $folder.
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected static function addToTree(array &$tree, Folder $folder) {
			$folderTree = explode('-', $folder->getTree());
			$folderTree = array_filter($folderTree);
			if (count($folderTree) == 1) {
				$tree[$folder->getId()] = $folder;
			} else {
				try {
					$currentFolder = $tree[intval($folderTree[0], 16)];
					for ($i = 1; $i < count($folderTree) - 1; $i++) {
						$currentFolder = $currentFolder->getChild(intval($folderTree[$i], 16));
					}
					$currentFolder->addChild($folder);
				} catch (FolderException $fe) {
					throw new FolderException(sprintf('Error while adding folder to tree. Current folder id: %d, child folder id: %d', $currentFolder->getId(), $folder->getId()));
				}
			}
		}
	}
?>