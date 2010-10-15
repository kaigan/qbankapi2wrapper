<?php
	class CommunicationException extends QBankAPIException {
		protected $type;
		
		public function __construct($message = null, $code = 0, $type = 'Exception') {
			$this->type = $type;
			parent::__construct($message, $code);
		}
		
		public function getType() {
			return $this->type;
		}
	}
?>