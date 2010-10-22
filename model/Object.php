<?php
	require_once 'model/IHasProperties.php';
	require_once 'model/Property.php';
	
	require_once 'exceptions/PropertyException.php';
	
	/**
	 * Represents an object in QBank.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 */
	class Object implements IHasProperties {
		
		/**
		 * The id of the object.
		 * @var int
		 */
		protected $id;
		
		/**
		 * The name of the object.
		 * @var string
		 */
		protected $name;
		
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
		 * The unix timestamp of when the object was last uploaded.
		 * @var int
		 */
		protected $uploaded;
		
		/**
		 * The id of the objects category.
		 * @var int
		 */
		protected $categoryId;
		
		/**
		 * The current version of the object.
		 * @var string
		 */
		protected $version;
		
		/**
		 * The id of the user owning the object.
		 * @var int
		 */
		protected $ownerId;
		
		/**
		 * If the object is deleted.
		 * @var bool
		 */
		protected $deleted;
		
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
		 * The height of the objects preview image in pixels.
		 * @var int
		 */
		protected $previewWidth;
		
		/**
		 * The height of the objects preview image in pixels.
		 * @var int
		 */
		protected $previewHeight;
		
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
		 * The colorspace of the object.
		 * @var string
		 */
		protected $colorspace;
		
		/**
		 * The ICC profile of the of the object.
		 * @var string
		 */
		protected $icc;
		
		/**
		 * The number of pixels per inch width-wise of the object.
		 * @var int
		 */
		protected $resolutionWidth;
		
		/**
		 * The number of pixels per inch height-wise of the object.
		 * @var int
		 */
		protected $resolutionHeight;
		
		/**
		 * The length of the object.
		 * @var string
		 */
		protected $length;
		
		/**
		 * The bits per second of the object.
		 * @var string
		 */
		protected $bitsPerSecond;
		
		/**
		 * The {@link Property}(ies) of this object.
		 * @var array
		 */
		protected $properties;
		
		/**
		 * Creates a new object.
		 * @param int $id The id of the object.
		 * @param string $name The name of the object.
		 * @param int $created The unix timestamp of when the object was created.
		 * @param int $updated The unix timestamp of when the object was last updated.
		 * @param int $uploaded The unix timestamp of when the object was last uploaded.
		 * @param int $categoryId The id of the propertys category.
		 * @param string $version The current version of the object.
		 * @param int $ownerId The id of the user owning the object.
		 * @param string $filename The filename of the object.
		 * @param string $mimetype The mime type of the object.
		 * @param string $fileExtension The file extension of the object.
		 * @param string $filenameOfHashedThumbnail The filename of the hashed thumbnail.
		 * @param int $filesize The size of the object in bytes.
		 * @param bool $deleted If the object is deleted.
		 * @author Björn Hjortsten
		 * @return Object
		 */
		public function __construct($id, $name, $created, $updated, $uploaded, $categoryId, $version, $ownerId, $filename, $mimetype,
									$fileExtension, $filenameOfHashedThumbnail, $filesize = 0, $deleted = false) {
			$this->id = $id;
			$this->name = $name;
			$this->created = $created;
			$this->updated = $updated;
			$this->uploaded = $uploaded;
			$this->categoryId = $categoryId;
			$this->version = $version;
			$this->ownerId = $ownerId;
			$this->filename = $filename;
			$this->mimetype = $mimetype;
			$this->fileExtension = $fileExtension;
			$this->filenameOfHashedThumbnail = $filenameOfHashedThumbnail;
			$this->filesize = $filesize;
			$this->deleted = $deleted;
			
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
		 * Gets the current version of the object.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getVersion() {
			return $this->version;
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
		 * If the object is deleted.
		 * @author Björn Hjortsten
		 * @return bool
		 */
		public function isDeleted() {
			return $this->deleted;
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
		 * Gets the width of the objects preview image in pixels.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getPreviewWidth() {
			return $this->previewWidth;
		}
		
		/**
		 * Gets the height of the objects preview image in pixels.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getPreviewHeight() {
			return $this->previewHeight;
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
		 * Gets the colorspace of the object.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getColorspace() {
			return $this->colorspace;
		}
		
		/**
		 * Gets the ICC profile of the object.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getICCProfile() {
			return $this->icc;
		}
		
		/**
		 * Gets the number of pixels per inch width-wise of the object.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getResolutionWidth() {
			return $this->resolutionWidth;
		}
		
		/**
		 * Gets the number of pixels per inch height-wise of the object.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getResolutionHeight() {
			return $this->resolutionHeight;
		}
		
		/**
		 * Gets the length of the object.
		 * @internal Mostly used for video objects.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getLength() {
			return $this->length;
		}
		
		/**
		 * Gets the bits per second of the object.
		 * @internal Mostly used for video objects.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getBitsPerSecond() {
			return $this->bitsPerSecond;
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
		 * Creates an {@link Object} from an object directly from the API.
		 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
		 * @param stdClass $rawObject The raw object from the API-call.
		 * @author Björn Hjortsten
		 * @return Object
		 */
		public static function createFromRawObject(stdClass $rawObject) {
			$object = new Object(intval($rawObject->information->id), $rawObject->information->name, strtotime($rawObject->information->createdTime),
								 strtotime($rawObject->information->updatedTime), strtotime($rawObject->information->uploadTime), intval($rawObject->information->objectType),
								 $rawObject->information->version, intval($rawObject->information->owner), $rawObject->information->filename,
								 $rawObject->information->contentType, $rawObject->information->extension, $rawObject->information->filenameHashedThumb,
								 intval($rawObject->information->filesize), (bool) $rawObject->information->deleted);
			list($width, $height) = explode('x', $rawObject->information->imageWidthHeight);
			$object->width = intval($width);
			$object->height = intval($height);
			list($width, $height) = explode('x', $rawObject->information->previewWidthHeight);
			$object->previewWidth = intval($width);
			$object->previewHeight = intval($height);
			list($width, $height) = explode('x', $rawObject->information->thumbWidthHeight);
			$object->thumbnailWidth = intval($width);
			$object->thumbnailHeigth = intval($height);
			$object->colorspace = $rawObject->information->colorspace;
			$object->icc = $rawObject->information->iccprofile;
			list($width, $height) = explode('x', $rawObject->information->resolution);
			$object->resolutionWidth = intval($width);
			$object->resolutionHeight = intval($height);
			$object->length = $rawObject->information->length;
			$object->bitsPerSecond = $rawObject->information->bps;
			
			foreach ($rawObject->properties as $rawProperty) {
				$properties[] = Property::createFromRawObject($rawProperty);
			}
			$object->setProperties($properties);
			
			return $object;
		}
	}
?>