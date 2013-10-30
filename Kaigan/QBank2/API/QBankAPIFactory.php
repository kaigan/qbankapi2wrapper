<?php

namespace Kaigan\QBank2\API;

use \Kaigan\QBank2\API\Exception\APIException;
	
/**
 * A factory for creating API-objects.
 * Note: This 
 * @author Björn Hjortsten
 * @copyright Kaigan 2011
 */
class QBankAPIFactory {

	const FolderAPI		= 'Kaigan\\QBank2\\API\\FolderAPI';
	const MoodboardAPI	= 'Kaigan\\QBank2\\API\\MoodboardAPI';
	const ObjectAPI		= 'Kaigan\\QBank2\\API\\ObjectAPI';
	const SearchAPI		= 'Kaigan\\QBank2\\API\\SearchAPI';
	const AccountAPI	= 'Kaigan\\QBank2\\API\\AccountAPI';

	/**
	 * Creates a new fully initiated API-object of the supplied type.
	 * @param string $type The type of API-object to be created.
	 * @throws APIException Thrown if anything went wrong while creating the API-Object.
	 * @author Björn Hjortsten
	 * @return mixed An object of the supplied type.
	 */
	public static function createAPI($type) {
		@session_start();
		if (!isset($_SESSION['qbankapi']['address'])) {
			throw new APIException('Please set up the factory before you use it. Note that it is dependent on $_SESSION!');
		}
		if (!class_exists($type)) {
			throw new APIException('The type of API supplied was not found! Please check your parameters.');
		}
		$api = new $type($_SESSION['qbankapi']['address'], $_SESSION['qbankapi']['apiaddress']);
		if (isset($_SESSION['qbankapi']['hash'])) {
			$api->setHash($_SESSION['qbankapi']['hash']);
			if (!$api->isValidConnection()) {
				$api = self::login($api);
			}
		} else {
			$api = self::login($api);
		}
		return $api;
	}

	protected static function login($api) {
		$loggedIn = $api->login($_SESSION['qbankapi']['username'], $_SESSION['qbankapi']['password'], $_SESSION['qbankapi']['languageid']);
		if (!$loggedIn) {
			throw new APIException('Error while logging in to QBank! Either wrong username or password.');
		}
		$_SESSION['qbankapi']['hash'] = $api->getHash();
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
