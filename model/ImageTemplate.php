<?php
	/**
	 * Represents a QBank image template.
	 * An image template describes how an original image transforms when published.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 */
	class ImageTemplate {
		
		protected $name;
		protected $maxWidth;
		protected $maxHeight;
		protected $dpi;
		protected $extension;
		protected $aspectRatio;
		protected $quality;
		
		/**
		 * Creates a new ImageTemplate.
		 * @param string $name The name of the image template.
		 * @param int $maxWidth The maximum width that an image processed by the template will have.
		 * @param int $maxHeight The maximum height that an image processed by the template will have.
		 * @param string $extension The file extension that an image processed by the template will have.
		 * @param string $aspectRatio The aspect ratio that an image processed by the template will have.
		 * @param int $quality The quality of the recompression of an image processed by the template. (Only valid for jpg)
		 * @param int $dpi The resolution that an image processed by the template will have.
		 * @author Björn Hjortsten
		 * @return ImageTemplate
		 */
		public function __construct($name, $maxWidth, $maxHeight, $extension, $aspectRatio, $quality = 92, $dpi = 72) {
			$this->name = $name;
			$this->maxWidth = $maxWidth;
			$this->maxHeight = $maxHeight;
			$this->extension = $extension;
			$this->dpi = $dpi;
			$this->aspectRatio = $aspectRatio;
			$this->quality = $quality;
		}
		
		/**
		 * Gets the name of the image template.
		 * @author Björn Hjortsten
		 * return string
		 */
		public function getName() {
			return $this->name;
		}
		
		/**
		 * Gets the maximum width in pixels that an image processed by this template has.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getMaxWidth() {
			return $this->maxWidth;
		}
		
		/**
		 * Gets the maximum height in pixels that an image processed by this template has.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getMaxHeigth() {
			return $this->maxHeight;
		}
		
		/**
		 * Gets the file extension that an image processed by this template has.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getExtension() {
			return $this->extension;
		}
		
		/**
		 * Gets the resolution in pixels per square inch that an image processed by this template has.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getDPI() {
			return $this->dpi;
		}
		
		/**
		 * Gets the aspect ratio that an image processed by this template has.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getAspectRatio() {
			return $this->aspectRatio;
		}
		
		/**
		 * Gets the quality of recompression that an image processed by this template has.
		 * NOTE: Only valid for jpg.
		 * @author Björn Hjortsten
		 * @return int
		 */
		public function getQuality() {
			return $this->quality;
		}
	}
?>