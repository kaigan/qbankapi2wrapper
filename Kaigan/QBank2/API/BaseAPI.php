<?php

namespace Kaigan\QBank2\API;

use \Kaigan\QBank2\API\Exception\APIException;
use \Kaigan\QBank2\API\Exception\CommunicationException;
use \Kaigan\QBank2\API\Exception\ConnectionException;
use \Monolog\Handler\AbstractHandler;
use \Monolog\Handler\ErrorLogHandler;
use \Monolog\Logger;
	
/**
 * Base class for the QBank API. Provides basic functionality.
 * @author Björn Hjortsten
 * @copyright Kaigan 2010
 */
abstract class BaseAPI {

	/**
	 * The version of QBankAPIWrapper.
	 * @var string
	 */
	const VERSION = '1.3.0';

	protected $apiAddress;
	protected $qbankAddress;
	protected $curlHandle;
	protected $requestTimeout;
	protected $hash;
	protected $useSSL;
	protected $lastCall;
	protected $lastCallInfo;
	
	/**
	 * @var Logger
	 */
	protected $callLog;
	
	/**
	 * @var Logger
	 */
	protected $wrapperLog;

	/**
	 * Sets up the class and prepares for calls to the QBank API.
	 * @param string $qbankAddress The address to the qbank being called.
	 * @param string @apiAddress The address to the api being called.
	 * @throws APIException Thrown if unable to access or create logfiles.
	 * @author Björn Hjortsten
	 * @return BaseAPI
	 */
	public function __construct($qbankAddress, $apiAddress = null) {
		$this->qbankAddress = $qbankAddress;
		if (empty($apiAddress)) {
			$this->apiAddress = 'http://api2.qbank.se';
		} else {
			$this->apiAddress = $apiAddress;
		}
		$this->curlHandle = curl_init();
		$this->requestTimeout = 10;
		$this->lastCall = null;
		$this->lastCallInfo = array();
		$this->useSSL(false);						// Do not use SSL as default

		$this->callLog = new Logger('api-calls');
		$this->callLog->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::NOTICE));
		$this->wrapperLog = new Logger('wrapper');
		$this->wrapperLog->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::NOTICE));
	}

	/**
	 * Sets the timeout for calls to the QBank API.
	 * @param int $seconds The maximum time a call can take in seconds.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setTimeout($seconds) {
		$this->requestTimeout = intval($seconds);
	}

	/**
	 * Sets the connection hash.
	 * @param string $hash The connection hash.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function setHash($hash) {
		$this->hash = $hash;
	}
	
	public function addLogHandler(Monolog\Handler\AbstractHandler $handler) {
		$this->callLog->pushHandler($handler);
		$this->wrapperLog->pushHandler($handler);
	}

	/**
	 * Gets the connection hash.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getHash() {
		return $this->hash;
	}

	/**
	 * Logs a user in to QBank.
	 * @internal Do NOT save this info in the class anywhere!
	 * @param string $username The users username.
	 * @param string $password The users password.
	 * @param int $languageId The language id to use.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @author Björn Hjortsten
	 * @return bool True if the login was successfull, false if not.
	 */
	public function login($username, $password, $languageId = null) {
		$data = array('username' => $username, 'password' => $password);
		if (!empty($languageId)) {
			$data['languageId'] = intval($languageId);
		}
		try {
			$result = $this->call('login', $data);
		} catch (CommunicationException $ce) {
			$this->wrapperLog->warning('Faulty login.', array('username' => $username));
			return false;
		}
		$this->wrapperLog->info('Login.', array('hash' => $result->hash));
		$this->setHash($result->hash);
		return true;
	}

	/**
	 * Set whether the connection should use SSL or not.
	 * The default is to not use SSL.
	 * @param bool $bool
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function useSSL($bool) {
		$apiAddress = parse_url($this->apiAddress);
		if ($bool === true) {
			$apiAddress['scheme'] = 'https';
		} else {
			$apiAddress['scheme'] = 'http';
		}
		@$this->apiAddress = $apiAddress['scheme'].'://'.$apiAddress['host'].$apiAddress['path'];
		$this->useSSL = $bool;
	}

	public function isValidConnection(){
		try {
			$result = $this->call('emptyfunction', array('objectId' => 0));
			return true;
		} catch (CommunicationException $ce) {
			return false;
		}
	}

	/**
	 * Executes a call to the QBank API.
	 * @internal Uses Curl to communicate.
	 * @param string $function The name of the API-function to call.
	 * @param array $data The data to be sent to the called API-function. Usually normal PHP-arrays or objects.
	 * @param bool $log Whether to log the call. Mainly used for debugging.
	 * @param string $pathToFile A path to a file to send. Used when uploading.
	 * @throws ConnectionException Thrown if something went wrong with the connection.
	 * @throws CommunicationException Thrown if the call returned an exception.
	 * @throws APIException Thrown when $pathToFile is on the wrong format.
	 * @author Björn Hjortsten
	 * @return mixed The result of a successfull call in the form of an object or array where applicable. If buffering is enabled, it will return the ticket name.
	 */
	protected function call($function, $data, $log = false, $pathToFile = null) {
		if (!empty($this->hash) && strtolower($function) != 'login') {
			$data['hash'] = $this->hash;
		}
		$json = json_encode($data);
		if ($pathToFile != null) {
			$path = realpath($pathToFile);
			if (!is_file($path)) {
				$this->wrapperLog->critical('Error while uploading. The supplied path is not a path to a file!', array('path' => $path));
				throw new APIException('The supplied path "'.$pathToFile.'" is not a path to a file!');
			}
			if (!is_readable($path)) {
				$this->wrapperLog->critical('Error while uploading. The supplied path is not readable!', array('path' => $path));
				throw new APIException('The supplied path "'.$pathToFile.'" is not readable!');
			}
			$data = array(
				'data' => $json,
				'file' => '@'.$path
			);
		} else {
			$data = array(
				'data' => $json
			);
		}
		$url = sprintf('%s/%s/%s', $this->apiAddress, $this->qbankAddress, $function);
		curl_setopt($this->curlHandle, CURLOPT_URL, $url);
		curl_setopt($this->curlHandle, CURLOPT_POST, true);
		curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $data);
		curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->curlHandle, CURLOPT_FAILONERROR, true);
		curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, $this->requestTimeout);
		if ($this->useSSL === true) {
			curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, false);
		}
		curl_setopt($this->curlHandle, CURLOPT_USERAGENT, 'QBankAPIWrapper '.BaseAPI::VERSION);
		$resultJSON = curl_exec($this->curlHandle);
		$this->lastCallInfo = curl_getinfo($this->curlHandle);
		if ($resultJSON === false) {
			$error = sprintf('Error while comunicating with QBank: %s', curl_error($this->curlHandle));
			$this->lastCall = $error;
			curl_close($this->curlHandle);
			$this->curlHandle = curl_init();
			$this->callLog->critical($error, array('address' => $this->apiAddress.'/'.$this->qbankAddress.'/'.$function, 'request' => $json));
			throw new ConnectionException($error, curl_errno($this->curlHandle));
		} else {
			$this->lastCall = $resultJSON;
			$result = json_decode($resultJSON);
			if (!isset($result->success) || $result->success === false) {
				if (isset($result->error)) {
					$this->callLog->critical('Error response from the API. '.$result->error->message, array(
						'code' => $result->error->code,
						'type' => $result->error->type,
						'address' => $this->apiAddress.'/'.$this->qbankAddress.'/'.$function,
						'request' => $json,
						'response' => $resultJSON
					));
					throw new CommunicationException($result->error->message, $result->error->code, $result->error->type);
				} else {
					$this->callLog->critical('Non-successful call to QBank API and no specified error.', array(
						'address' => $this->apiAddress.'/'.$this->qbankAddress.'/'.$function,
						'request' => $json,
						'response' => $resultJSON
					));
					throw new CommunicationException('Unknown error! Non-successful call to QBank API and no specified error. Please note the time and report this to support@kaigantbk.se', 99, 'UnknownError');
				}
			}
			if ($log === true) {
				$this->callLog->notice('API call.', array(
					'address' => $this->apiAddress.'/'.$this->qbankAddress.'/'.$function,
					'request' => $json,
					'response' => $resultJSON
				));
			} else {
				$this->callLog->debug('API call.', array(
					'address' => $this->apiAddress.'/'.$this->qbankAddress.'/'.$function,
					'request' => $json,
					'response' => $resultJSON
				));
			}
			return $result;
		}
	}

	protected function callAsync($function, array $data, $log = false) {
		if (strtolower($function) == 'login') {
			$this->wrapperLog->error('Login can not be called asynchronously!');
			throw new APIException('Login can not be called asynchronously!');
		}
		if (!empty($this->hash)) {
			$data['hash'] = $this->hash;
		}

		$socket = fsockopen(parse_url($this->apiAddress, PHP_URL_HOST), 80, $errno, $errstr);
		if ($socket === false) {
			$this->wrapperLog->critical('Error while opening asynchronous socket: '.$errstr, array('code' => $errno));
			throw new ConnectionException('Error while opening asynchronous socket: '.$errstr, $errno);
		}

		$data = 'data='.urlencode(json_encode($data));

		$msg = 'POST /'.$this->qbankAddress.'/'.$function.' HTTP/1.1'."\r\n";
		$msg .= 'Host:'.parse_url($this->apiAddress, PHP_URL_HOST)."\r\n";
		$msg .= 'Content-type: application/x-www-form-urlencoded'."\r\n";
		$msg .= 'Content-length: '.strlen($data)."\r\n";
		$msg .= 'Connection: Close'."\r\n\r\n";
		$msg .= $data;

		$result = fwrite($socket, $msg);
		if ($result === false) {
			$this->wrapperLog->critical('Error while writing to asycnhronous socket!');
			throw new ConnectionException('Error while writing to asycnhronous socket!');
		}
		@fclose($socket);
	}

	/**
	 * Returns the last result in its raw form. Normally this will be a JSON-string, but may be any string.
	 * Will return NULL if no calls have been made.
	 * @author Björn Hjortsten
	 * @return string
	 */
	public function getRawResult() {
		return $this->lastCall;
	}

	/**
	 * Returns information from cURL about the last call.
	 * Will return NULL if no calls have been made.
	 * @author Björn Hjortsten
	 * @see curl_getinfo()
	 * @return array An associative array containing lots of info.
	 */
	public function getCallInfo() {
		return $this->lastCallInfo;
	}

	/**
	 * Frees resources and closes data connections.
	 * @author Björn Hjortsten
	 * @return void
	 */
	public function __destruct() {
		curl_close($this->curlHandle);
	}
}

/**
 * Silly wrapper function since empty() does not work properly with return values.
 * @param mixed $var
 * @see empty()
 * @author Björn Hjortsten
 * @return bool
 */
function is_empty($var) {
	return empty($var);
}
