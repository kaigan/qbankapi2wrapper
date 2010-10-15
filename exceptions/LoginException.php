<?php
	require_once 'QBankAPIException.php';
	
	class LoginException extends QBankAPIException {
		public function __construct($message = null, $code = 0) {
			parent::__construct($message, $code);
		}
	}
?>