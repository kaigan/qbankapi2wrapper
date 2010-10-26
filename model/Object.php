<?php
	require_once 'model/SimpleObject.php';	

	/**
	 * Represents an object in QBank.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @see SimpleObject
	 */
	class Object extends SimpleObject {
		
		/**
		 * The unix timestamp of when the object was last uploaded.
		 * @var int
		 */
		protected $uploaded;
		
		/**
		 * The current version of the object.
		 * @var string
		 */
		protected $version;
		
		/**
		 * If the object is deleted.
		 * @var bool
		 */
		protected $deleted;
		
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
		 * Creates a new object.
		 * @param int $id The id of the object.
		 * @param int $mediaId The id of the objects media.
		 * @param string $name The name of the object.
		 * @param int $created The unix timestamp of when the object was created.
		 * @param int $updated The unix timestamp of when the object was last updated.
		 * @param int $uploaded The unix timestamp of when the object was last uploaded.
		 * @param int $categoryId The id of the propertys category.
		 * @param string $categoryName The name of the propertys category.
		 * @param string $version The current version of the object.
		 * @param int $ownerId The id of the user owning the object.
		 * @param string $filename The filename of the object.
		 * @param string $mimetype The mime type of the object.
		 * @param string $fileExtension
		 * @param string $filenameOfHashedThumbnail The filename of the hashed thumbnail.
		 * @param int $filesize The size of the object in bytes.
		 * @param bool $deleted If the object is deleted.
		 * @author Björn Hjortsten
		 * @return Object
		 */
		public function __construct($id, $mediaId, $name, $created, $updated, $uploaded, $categoryId, $categoryName, $version, $ownerId, $filename, $mimetype,
									$fileExtension, $filenameOfHashedThumbnail, $filesize = 0, $deleted = false) {
			$this->uploaded = $uploaded;
			$this->version = $version;
			$this->deleted = $deleted;
			
			parent::__construct($id, $mediaId, $name, $created, $updated, $categoryId, $categoryName, $ownerId, $filename, $mimetype, $fileExtension, $filenameOfHashedThumbnail, $filesize);
			
			$properties = array();
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
		 * If the object is deleted.
		 * @author Björn Hjortsten
		 * @return bool
		 */
		public function isDeleted() {
			return $this->deleted;
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
		 * Creates an {@link Object} from an object directly from the API.
		 * WARNING: If this is called with the wrong raw object, you may get warnings or even errors!
		 * @param stdClass $rawObject The raw object from the API-call.
		 * @author Björn Hjortsten
		 * @return Object
		 */
		public static function createFromRawObject(stdClass $rawObject) {
			$object = new Object(intval($rawObject->information->id), intval($rawObject->information->mediaId), $rawObject->information->name, strtotime($rawObject->information->createdTime),
								 strtotime($rawObject->information->updatedTime), strtotime($rawObject->information->uploadTime), intval($rawObject->information->objectType),
								 $rawObject->information->objectTypeName, $rawObject->information->version, intval($rawObject->information->owner), $rawObject->information->filename,
								 $rawObject->information->contentType, $rawObject->information->extension, $rawObject->information->filenameHashedThumb,
								 intval($rawObject->information->filesize), (bool) $rawObject->information->deleted);
			@list($width, $height) = explode('x', $rawObject->information->imageWidthHeight);
			$object->width = intval($width);
			$object->height = intval($height);
			@list($width, $height) = explode('x', $rawObject->information->previewWidthHeight);
			$object->previewWidth = intval($width);
			$object->previewHeight = intval($height);
			@list($width, $height) = explode('x', $rawObject->information->thumbWidthHeight);
			$object->thumbnailWidth = intval($width);
			$object->thumbnailHeigth = intval($height);
			$object->colorspace = $rawObject->information->colorspace;
			$object->icc = $rawObject->information->iccprofile;
			@list($width, $height) = explode('x', $rawObject->information->resolution);
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