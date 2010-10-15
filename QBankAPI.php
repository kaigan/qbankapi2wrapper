<?php
	require_once 'exceptions/ConnectionException.php';
	require_once 'exceptions/CommunicationException.php';
	
	require_once 'SimpleFolder.php';
	require_once 'Folder.php';
	
	class QBankAPI {
		
		protected $qbankAddress;
		protected $curlHandle;
		protected $requestTimeout;
		protected $hash;
		
		public function __construct($qbankAddress) {
			session_start();
			$this->qbankAddress = $qbankAddress;
			$this->curlHandle = curl_init();
			$this->requestTimeout = 10;
			if (isset($_SESSION['api_hash']) && !empty($_SESSION['api_hash'])) {
				$this->hash = $_SESSION['api_hash'];
			}
		}
		
		public function setTimeout($seconds) {
			$this->requestTimeout = intval($seconds);
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
			$this->hash = $result->hash;
			$_SESSION['api_hash'] = $this->hash;
			return true;
		}
		
		/**
		 * Gets folders from QBank.
		 * Default is to get all folders.
		 * @param int $rootFolder The folder to consider as root.
		 * @param int $depth How many levels of folders to get.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return mixed An array of {@link SimpleFolder}s if, or null if there are no results.
		 */
		public function getFolders($rootFolderId = null, $depth = null) {
			if ($rootFolderId == null) {
				$rootFolderId = 0;
			}
			if ($depth == null) {
				// Since there is no way to fetch for an infinite depth, we fetch for the maximum logical depth
				$depth = 23;
			}
			$data = array('folderId' => $rootFolderId, 'depth' => $depth);
			$result = $this->call('getfolderstructure', $data);
			if (is_array($result->data)) {
				foreach ($result->data as $folder) {
					$folders[] = new SimpleFolder($folder->name, $folder->tree, $folder->owner, strtotime($folder->created), strtotime($folder->updated));
				}
			}
			return $folders;
		}
		
		/**
		 * Executes a call to the QBank API.
		 * @internal Uses Curl to communicate.
		 * @param string $function The name of the API-function to call.
		 * @param array $data The data to be sent to the called API-function. Usually normal PHP-arrays or objects.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @throws CommunicationException Thrown if the call returned an exception.
		 * @author Björn Hjortsten
		 * @return mixed The result of a successfull callin in the form of an object or array where applicable.
		 */
		protected function call($function, $data) {
			if (!empty($this->hash)) {
				$data['hash'] = $this->hash;
			}
			$data = json_encode($data);
			$url = sprintf('http://api2.qbank.se/%s/%s?data=%s', $this->qbankAddress, $function, $data);
			curl_setopt($this->curlHandle, CURLOPT_URL, $url);
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
				if ($result->success === false) {
					throw new CommunicationException($result->error->message, $result->error->code, $result->error->type);
				}
				return $result;
			}
		}
		
		public function __destruct() {
			curl_close($this->curlHandle);
		}
	}
?>