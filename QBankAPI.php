<?php
	require_once 'exceptions/QBankAPIException.php';
	require_once 'exceptions/ConnectionException.php';
	require_once 'exceptions/CommunicationException.php';
	
	/**
	 * Base class for the QBank API. Provides basic functionality.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 * @package QBankAPIWrapper
	 */
	abstract class QBankAPI {
		
		/**
		 * The version of QBankAPIWrapper.
		 * @var string
		 */
		const VERSION = '1.1.44';
		
		const CALLS_LOG = '/var/log/qbankapiwrapper/calls.log';
		const UNKNOWNS_LOG = '/var/log/qbankapiwrapper/unknowns.log';
		
		protected $apiAddress;
		protected $qbankAddress;
		protected $curlHandle;
		protected $requestTimeout;
		protected $hash;
		protected $useSSL;
		protected $lastCall;
		
		/**
		 * Sets up the class and prepares for calls to the QBank API.
		 * @param string $qbankAddress The address to the qbank being called.
		 * @param string @apiAddress The address to the api being called.
		 * @throws QBankAPIException Thrown if unable to access or create logfiles.
		 * @author Björn Hjortsten
		 * @return QBankAPI
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
			$this->useSSL(false);						// Do not use SSL as default
			
			// Check for logfiles
			// Does the log directories exist?
			if (!is_dir(dirname(QBankAPI::CALLS_LOG))) {
				if (@mkdir(dirname(QBankAPI::CALLS_LOG), 0774) === false) {
					throw new QBankAPIException('Could not create the calls log file folder!');
				}
			}
			if (!is_dir(dirname(QBankAPI::UNKNOWNS_LOG))) {
				if (@mkdir(dirname(QBankAPI::UNKNOWNS_LOG), 0774) === false) {
					throw new QBankAPIException('Could not create the unknown calls log file folder!');
				}
			}
			
			// Does the calls log file exist?
			if (!is_writable(QBankAPI::CALLS_LOG)) {
				$calls = @fopen(QBankAPI::CALLS_LOG, 'ab');
				if ($calls === false) {
					@fclose($calls);
					throw new QBankAPIException('Could not create the log file!');
				}
				@fclose($calls);
			}
			
			// Does the unknowns log file exist?
			if (!is_writable(QBankAPI::UNKNOWNS_LOG)) {
				$calls = @fopen(QBankAPI::UNKNOWNS_LOG, 'ab');
				if ($calls === false) {
					@fclose($calls);
					throw new QBankAPIException('Could not create the log file!');
				}
				@fclose($calls);
			}
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
				return false;
			}
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
		
		/**
		 * Executes a call to the QBank API.
		 * @internal Uses Curl to communicate.
		 * @param string $function The name of the API-function to call.
		 * @param array $data The data to be sent to the called API-function. Usually normal PHP-arrays or objects.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @throws CommunicationException Thrown if the call returned an exception.
		 * @author Björn Hjortsten
		 * @return mixed The result of a successfull call in the form of an object or array where applicable. If buffering is enabled, it will return the ticket name.
		 */
		protected function call($function, $data, $log = false) {
			if (!empty($this->hash) && strtolower($function) != 'login') {
				$data['hash'] = $this->hash;
			}
			$json = json_encode($data);
			$data = 'data='.urlencode($json);
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
			curl_setopt($this->curlHandle, CURLOPT_USERAGENT, 'QBankAPIWrapper '.QBankAPI::VERSION);
			$resultJSON = curl_exec($this->curlHandle);
			if ($resultJSON === false) {
				$error = sprintf('Error while comunicating with QBank: %s', curl_error($this->curlHandle));
				$this->lastCall = $error;
				curl_close($this->curlHandle);
				$this->curlHandle = curl_init();
				error_log(sprintf('[%s] (%s) %s: %s'."\n",date('Y-m-d H:i:s'), 'ERROR', $this->apiAddress.'/'.$this->qbankAddress.'/'.$function, $json), 3, QBankAPI::CALLS_LOG);
				throw new ConnectionException($error, curl_errno($this->curlHandle));
			} else {
				$this->lastCall = $resultJSON;
				$result = json_decode($resultJSON);
				if (!isset($result->success) || $result->success === false) {
					if (isset($result->error)) {
						error_log(sprintf('[%s] (%s) %s: %s'."\n",date('Y-m-d H:i:s'), 'ERROR', $this->apiAddress.'/'.$this->qbankAddress.'/'.$function, $json), 3, QBankAPI::CALLS_LOG);
						throw new CommunicationException($result->error->message, $result->error->code, $result->error->type);
					} else {
						error_log(sprintf('[%s] (%s) %s: %s'."\n\t".'Response: %s'."\n", date('Y-m-d H:i:s'), 'UNKNOWN ERROR', $this->apiAddress.'/'.$this->qbankAddress.'/'.$function, $json, $resultJSON), 3, QBankAPI::UNKNOWNS_LOG);
						throw new CommunicationException('Unknown error! Non-successful call to QBank API and no specified error. Please note the time and report this to support@kaigantbk.se', 99, 'UnknownError');
					}
				}
				if ($log === true) {
					error_log(sprintf('[%s] (%s) %s: %s'."\n",date('Y-m-d H:i:s'), 'INFO', $this->apiAddress.'/'.$this->qbankAddress.'/'.$function, $json), 3, QBankAPI::CALLS_LOG);
				}
				return $result;
			}
		}
		
		protected function callAsync($function, array $data, $log = false) {
			if (strtolower($function) == 'login') {
				throw new QBankAPIException('Login can not be called asynchronously!');
			}
			if (!empty($this->hash)) {
				$data['hash'] = $this->hash;
			}
			
			$socket = fsockopen(parse_url($this->apiAddress, PHP_URL_HOST), 80, $errno, $errstr);
			if ($socket === false) {
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
?>