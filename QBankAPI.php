<?php
	require_once 'exceptions/ConnectionException.php';
	require_once 'exceptions/CommunicationException.php';
	
	/**
	 * Base class for the QBank API. Provides basic functionality.
	 * @author Björn Hjortsten
	 * @copyright Kaigan TBK 2010
	 */
	abstract class QBankAPI {
		
		protected $apiAddress;
		protected $qbankAddress;
		protected $curlHandle;
		protected $requestTimeout;
		protected $hash;
		
		/**
		 * Sets up the class and prepares for calls to the QBank API.
		 * @param string $qbankAddress The address to the qbank being called.
		 * @author Björn Hjortsten
		 * @return QBankAPI
		 */
		public function __construct($qbankAddress) {
			$this->qbankAddress = $qbankAddress;
			$this->curlHandle = curl_init();
			$this->requestTimeout = 10;
			$this->apiAddress = 'http://api2.qbank.se';
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
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return bool True if the login was successfull, empty if not.
		 */
		public function login($username, $password) {
			$data = array('username' => $username, 'password' => $password);
			try {
				$result = $this->call('login', $data);
			} catch (CommunicationException $ce) {
				return false;
			}
			$this->setHash($result->hash);
			return true;
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
			if ($log === true) {
				error_log(sprintf('[%s] %s: %s'."\n",date('Y-m-d H:i:s'), $function, json_encode($data)), 3, '/var/www/libs/qbankapi/logs/json.log');
			}
			$data = 'data='.urlencode(json_encode($data));
			$url = sprintf('%s/%s/%s', $this->apiAddress, $this->qbankAddress, $function);
			curl_setopt($this->curlHandle, CURLOPT_URL, $url);
			curl_setopt($this->curlHandle, CURLOPT_POST, true);
			curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $data);
			curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->curlHandle, CURLOPT_FAILONERROR, true);
			curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, $this->requestTimeout);
			curl_setopt($this->curlHandle, CURLOPT_USERAGENT, 'QBankAPIWrapper');
			$result = curl_exec($this->curlHandle);
			if ($result === false) {
				$error = sprintf('Error while comunicating with QBank: %s', curl_error($this->curlHandle));
				curl_close($this->curlHandle);
				$this->curlHandle = curl_init();
				throw new ConnectionException($error, curl_errno($this->curlHandle));
			} else {
				$result = json_decode($result);
				if (!isset($result->success) || $result->success === false) {
					if (isset($result->error)) {
						throw new CommunicationException($result->error->message, $result->error->code, $result->error->type);
					} else {
						throw new CommunicationException('Unknown error! Non-successful call to QBank API and no specified error.', 99, 'UnknownError');
					}
				}
				return $result;
			}
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
?>