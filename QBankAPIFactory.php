<?php
	require_once 'QBankFolderAPI.php';
	require_once 'QBankMoodboardAPI.php';
	require_once 'QBankObjectAPI.php';
	require_once 'QBankSearchAPI.php';
	
	/**
	 * A factory for creating API-objects.
	 * Note: This 
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2011
	 * @package QBankAPIWrapper
	 */
	class QBankAPIFactory {
		
		const FolderAPI		= 'QBankFolderAPI';
		const MoodboardAPI	= 'QBankMoodboardAPI';
		const ObjectAPI		= 'QBankObjectAPI';
		const SearchAPI		= 'QBankSearchAPI';
		
		/**
		 * Creates a new fully initiated API-object of the supplied type.
		 * @param string $type The type of API-object to be created.
		 * @throws QBankAPIException Thrown if anything went wrong while creating the API-Object.
		 * @author Björn Hjortsten
		 * @return mixed An object of the supplied type.
		 */
		public static function createAPI($type) {
			@session_start();
			if (!isset($_SESSION['qbankapi']['address'])) {
				throw new QBankAPIException('Please set up the factory before you use it. Note that it is dependent on $_SESSION!');
			}
			if (!class_exists($type)) {
				throw new QBankAPIException('The type of API supplied was not found! Please check your parameters.');
			}
			$api = new $type($_SESSION['qbankapi']['address'], $_SESSION['qbankapi']['apiaddress']);
			if (isset($_SESSION['qbankapi']['hash'])) {
				$api->setHash($_SESSION['qbankapi']['hash']);
			} else {
				$loggedIn = $api->login($_SESSION['qbankapi']['username'], $_SESSION['qbankapi']['password'], $_SESSION['qbankapi']['languageid']);
				if (!$loggedIn) {
					throw new QBankAPIException('Error while logging in to QBank! Either wrong username or password.');
				}
				$_SESSION['qbankapi']['hash'] = $api->getHash();
			}
			return $api;
		}
		
		/**
		 * Sets up the factory.
		 * @param string $qbankAddress The address of the QBank to connect to.
		 * @param string $username The username to login with.
		 * @param string $password The password to login with.
		 * @param int $languageId The language id to use.
		 * @author Björn Hjortsten
		 * @return void
		 */
		public static function setUp($qbankAddress, $username, $password, $languageId = null, $apiAddress = null) {
			@session_start();
			if (isset($_SESSION['qbankapi']['hash'])) {
				if ($_SESSION['qbankapi']['address'] != $qbankAddress || $_SESSION['qbankapi']['username'] != $username ||
					$_SESSION['qbankapi']['password'] != $password || $_SESSION['qbankapi']['languageid'] != $languageId ||
					$_SESSION['qbankapi']['apiaddress'] != $apiAddress) {
					unset($_SESSION['qbankapi']['hash']);
				}
			}
			$_SESSION['qbankapi']['address'] 	= $qbankAddress;
			$_SESSION['qbankapi']['username'] 	= $username;
			$_SESSION['qbankapi']['password'] 	= $password;
			if (!is_null($languageId)) {
				$_SESSION['qbankapi']['languageid'] = intval($languageId);
			} else {
				$_SESSION['qbankapi']['languageid'] = null;
			}
			if (!is_null($apiAddress)) {
				$_SESSION['qbankapi']['apiaddress'] = $apiAddress;
			} else {
				$_SESSION['qbankapi']['apiaddress'] = null;
			}
		}
	}
?>
