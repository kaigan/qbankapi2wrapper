<?php
	require_once 'model/IHasProperties.php';
	require_once 'model/Property.php';
	
	require_once 'exceptions/PropertyException.php';
	
	/**
	 * Represents a QBank object with the most basic information.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @see Object
	 * @see IHasProperties
	 * @package QBankAPIWrapper
	 */
	class SimpleObject implements IHasProperties {
		
		/**
		 * The id of the object.
		 * @var int
		 */
		protected $id;
		
		/**
		 * The id of the medias object.
		 * @var int
		 */
		protected $mediaId;
		
		/**
		 * The name of the object.
		 * @var string
		 */
		protected $name;
		
		/**
		 * The id of the objects category.
		 * @var int
		 */
		protected $categoryId;
		
		/**
		 * The name of the objects category.
		 * @var unknown_type
		 */
		protected $categoryName;
		
		/**
		 * The unix timestamp of when the object was created.
		 * @var int
		 */
		protected $created;
		
		/**
		 * The unix timestamp of when the object was last updated.
		 * @var int
		 */
		protected $updated;
		
		/**
		 * The id of the user owning the object.
		 * @var int
		 */
		protected $ownerId;
		
		/**
		 * The filename of the object.
		 * @var string
		 */
		protected $filename;
		
		/**
		 * The size of the object in bytes.
		 * @var int
		 */
		protected $filesize;
		
		/**
		 * The mime type of the object.
		 * @var string
		 */
		protected $mimetype;
		
		/**
		 * The file extension of the object.
		 * @var string
		 */
		protected $fileExtension;
		
		/**
		 * The filename of the hashed thumbnail.
		 * @var string
		 */
		protected $filenameOfHashedThumbnail;
		
		/**
		 * The width of the object in pixels.
		 * @var int
		 */
		protected $width;
		
		/**
		 * The height of the object in pixels.
		 * @var int
		 */
		protected $height;
		
		/**
		 * The width of the objects thumbnail in pixels.
		 * @var int
		 */
		protected $thumbnailWidth;
		
		/**
		 * The height of the objects thumbnail in pixels.
		 * @var int
		 */
		protected $thumbnailHeigth;
		
		/**
		 * The {@link Property}(ies) of this object.
		 * @var array
		 */
		protected $properties;
		
		/**
		 * Creates a new SimpleObject.
		 * @param int $id The id of the object.
		 * @param int $mediaId The id of the objects media.
		 * @param string $name The name of the object.
		 * @param int $created The unix timestamp of when the object was created.
		 * @param int $updated The unix timestamp of when the object was last updated.
		 * @param int $categoryId The id of the propertys category.
		 * @param string $categoryName The name of the propertys category.
		 * @param int $ownerId The id of the user owning the object.
		 * @param string $filename The filename of the object.
		 * @param string $mimetype The mime type of the object.
		 * @param string $fileExtension The file extension of the object.
		 * @param string $filenameOfHashedThumbnail The filename of the hashed thumbnail.
		 * @param int $filesize The size of the object in bytes.
		 */
		public function __construct($id, $mediaId, $name, $created, $updated, $categoryId, $categoryName, $ownerId, $filename, $mimetype,
									$fileExtension, $filenameOfHashedThumbnail, $filesize = 0) {
			$this->id = $id;
			$this->mediaId = $mediaId;
			$this->name = $name;
			$this->created = $created;
			$this->updated = $updated;
			$this->categoryId = $categoryId;
			$this->categoryName = $categoryName;
			$this->ownerId = $ownerId;
			$this->filename = $filename;
			$this->mimetype = $mimetype;
			$this->fileExtension = $fileExtension;
			$this->filenameOfHashedThumbnail = $filenameOfHashedThumbnail;
			$this->filesize = $filesize;
			
			$properties = array();
		}
		
		/**
		 * Gets the id of the object.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getId() {
			return $this->id;
		}
		
		/**
		 * Gets the id of the objects media.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getMediaId() {
			return $this->mediaId;
		}
		
		/**
		 * Gets the name of the object.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}
		
		/**
		 * Gets the unix timestamp from the objects creation.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getCreated() {
			return $this->created;
		}
		
		/**
		 * Gets the unix timestamp from when the object was last updated.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getUpdated() {
			return $this->updated;
		}
		
		/**
		 * Gets the id of the objects category.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getCategoryId() {
			return $this->categoryId;
		}
		
		/**
		 * Gets the name of the objects category.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getCategoryName() {
			return $this->categoryName;
		}
		
		/**
		 * Gets the id of the user owning the object.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getOwnerId() {
			return $this->ownerId;
		}
		
		/**
		 * Gets the filename of the object.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getFilename() {
			return $this->filename;
		}
		
		/**
		 * Gets the size of the object in bytes.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getFilesize() {
			return $this->filesize;
		}
		
		/**
		 * Gets the mimetype of the object.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getMimetype() {
			return $this->mimetype;
		}
		
		/**
		 * Gets the file extension of the object.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getFileExtension() {
			return $this->fileExtension;
		}
		
		/**
		 * Gets the filename of the hashed thumbnail.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getFilenameOfHashedThumbnail() {
			return $this->filenameOfHashedThumbnail;
		}
		
		/**
		 * Gets the width of the object in pixels.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getWidth() {
			return $this->width;
		}
		
		/**
		 * Gets the height of the object in pixels.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getHeight() {
			return $this->height;
		}
		
		/**
		 * Gets the width of the objects thumbnail image in pixels.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getThumbnailWidth() {
			return $this->thumbnailWidth;
		}
		
		/**
		 * Gets the height of the objects thumbnail image in pixels.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getThumbnailHeight() {
			return $this->thumbnailHeigth;
		}
		
	/**
		 * Gets all properties of the object.
		 * @author Björn Hjortsten
		 * @see IHasProperties::getProperties()
		 * @return array An array of {@link Property}(ies).
		 */
		public function getProperties() {
			return $this->properties;
		}
		
		/**
		 * Gets a {@link Property} of the object.
		 * @param mixed $identifier Either the system name of the property or the propertys id.
		 * @throws PropertyException Thrown if there is no property with the specified identifier.
		 * @author Björn Hjortsten
		 * @see IHasProperties::getProperty()
		 * @return Property
		 */
		public function getProperty($identifier) {
			if (is_numeric($identifier)) {
				foreach ($this->properties as $property) {
					if ($property->getId() == $identifier) {
						return $property;
					}
				}
				throw new PropertyException(sprintf('No such property with id %d', $identifier));
			} else {
				if (!isset($this->properties[$identifier])) {
					throw new PropertyException(sprintf('No such property with system name %s.', $identifier));
				}
				return $this->properties[$identifier];
			}
		}
		
		/**
		 * Adds a property to the object.
		 * @param Property $property The property to add.
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected function addProperty(Property $property) {
			$this->properties[$property->getSystemName()] = $property;
		}
		
		/**
		 * Adds several properties to the object.
		 * @param array $properties An array of {@link Property}(ies) to be added to the object.
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
		 * Sets all the properties of the object.
		 * @param array $properties An array of {@link Property}(ies) to be added to the object.
		 * @author Björn Hjortsten
		 * @return void
		 */
		protected function setProperties(array $properties) {
			$this->properties = array();
			$this->addProperties($properties);
		}
		
		/**
		 * Creates a {@link SimpleObject} from an object directly from the API.
		 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
		 * @param stdClass $rawObject The raw object from the API-call.
		 * @author Björn Hjortsten
		 * @return SimpleObject
		 */
		public static function createFromRawObject(stdClass $rawObject) {
			$object = new SimpleObject(intval($rawObject->objectId), intval($rawObject->mediaId), $rawObject->objectName, strtotime($rawObject->createdTime),
									   strtotime($rawObject->updatedTime), intval($rawObject->objectType), $rawObject->objectTypeName,
									   intval($rawObject->owner), $rawObject->filename, $rawObject->filetype,
									   $rawObject->extension, $rawObject->filenameHashedThumb, intval($rawObject->filesize));
			@list($width, $height) = explode('x', $rawObject->imageWidthHeight);
			$object->width = intval($width);
			$object->height = intval($height);
			@list($width, $height) = explode('x', $rawObject->thumbWidthHeight);
			$object->thumbnailWidth = intval($width);
			$object->thumbnailHeigth = intval($height);
			
			if (isset($rawObject->properties) && (is_array($rawObject->properties) || is_object($rawObject->properties))) {
				foreach ($rawObject->properties as $rawProperty) {
					$properties[] = Property::createFromRawObject($rawProperty);
				}
				$object->setProperties($properties);
			}
			return $object;
		}
	}
?>
