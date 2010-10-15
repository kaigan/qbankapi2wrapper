<?php
	require_once 'QBankAPIException.php';
	
	class ConnectionException extends QBankAPIException {
		public function __construct($message = null, $code = 0) {
			parent::__construct($message, $code);
		}
	}
?>