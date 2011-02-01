<?php
	require_once 'QBankAPIException.php';
	
	/**
	 * Represents an error in the connection to QBank.
	 * Probably not related to QBank but to the network.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @package QBankAPIWrapper
	 */
	class ConnectionException extends QBankAPIException {
	}
?>