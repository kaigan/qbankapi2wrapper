<?php
	
	/**
	 * Represents an error in communication with QBank.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 */
	class CommunicationException extends QBankAPIException {
		protected $type;
		
		/**
		 * Creates a new CommunicationException.
		 * @param string $message The message returned from QBank.
		 * @param int $code The error code returned from QBank.
		 * @param string $type The name of the {@link Exception} that occured in QBank while processing the call.
		 * @author Björn Hjortsten
		 * @return CommunicationException
		 */
		public function __construct($message = null, $code = 0, $type = 'Exception') {
			$this->type = $type;
			parent::__construct($message, $code);
		}
		
		/**
		 * Gets the name of the {@link Exception} that occured in QBank while processing the call.
		 * @author Björn Hjortsten
		 * @return string
		 */
		public function getType() {
			return $this->type;
		}
	}
?>