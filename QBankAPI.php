<?php
	require_once 'exceptions/ConnectionException.php';
	require_once 'exceptions/CommunicationException.php';
	
	require_once 'SimpleFolder.php';
	require_once 'Folder.php';
	require_once 'Property.php';
	
	class QBankAPI {
		
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
			session_start();
			$this->qbankAddress = $qbankAddress;
			$this->curlHandle = curl_init();
			$this->requestTimeout = 10;
			if (isset($_SESSION['api_hash']) && !empty($_SESSION['api_hash'])) {
				$this->hash = $_SESSION['api_hash'];
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
		 * @param bool $hierarchical If set to true, will return a hierachial list of {@link Folder}s.
		 * @throws ConnectionException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return mixed An array of {@link SimpleFolder}s or an array of top level {@link Folder}s if $hierarchial is set to true. Null if there are no results.
		 */
		public function getFolders($rootFolderId = null, $depth = null, $hierarchical = false) {
			if ($rootFolderId == null) {
				$rootFolderId = 0;
			}
			if ($depth == null) {
				// Since there is no way to fetch for an infinite depth, we fetch for the maximum logical depth
				$depth = 23;
			}
			$data = array('folderId' => $rootFolderId, 'depth' => $depth, 'fetchProperties' => $hierarchical);
			$result = $this->call('getfolderstructure', $data);
			if (is_array($result->data)) {
				foreach ($result->data as $folder) {
					if ($hierarchical === true) {
						$properties = array();
						foreach ($folder->properties as $property) {
							$properties[] = Property::createFromRawObject($property);
						}
						$folders[$folder->folderId] = new Folder($folder->name, $folder->tree, $folder->owner, strtotime($folder->created), strtotime($folder->updated), $properties);
					} else {
						$folders[$folder->folderId] = new SimpleFolder($folder->name, $folder->tree, $folder->owner, strtotime($folder->created), strtotime($folder->updated));
					}
				}
			}
			if ($hierarchical === true) {
				$folders = Folder::createTree($folders);
			}
			return $folders;
		}
		
		/**
		 * Gets a folder from QBank.
		 * @param int $id The folders id.
		 * @param bool $simple If true, gets {@link SimpleFolder}s, otherwise a {@link Folder}.
		 * @param bool $recursive If the folders subfolders also should be returned.
		 * @throws CommunicationException Thrown if something went wrong with the connection.
		 * @author Björn Hjortsten
		 * @return mixed If $simple is TRUE and it may return an array of {@link SimpleFolder}s or a {@link SimpleFolder} depending on $recursive. If $simple is FALSE it will return a {@link Folder}.
		 */
		public function getFolder($id, $simple = true, $recursive = false) {
			if ($recursive === true) {
				$calls[] = array('name' => 'subfolders', 'function' => 'getfolderstructure', 'arguments' => array('folderId' => $id, 'depth' => 23, 'fetchProperties' => !$simple));
			}
			$calls[] = array('name' => 'folder', 'function' => 'getfolderinformation', 'arguments' => array('folderId' => $id));
			$result = $this->call('batch', array('calls' => $calls));
			if ($result->results->folder->success !== true) {
				throw new CommunicationException($result->results->folder->error->message, $result->results->folder->error->code, $result->results->folder->error->type);
			}
			$folder = $result->results->folder->folder;
			if ($simple === true) {
				$folder = new SimpleFolder($folder->name, $folder->tree, $folder->owner, strtotime($folder->created), strtotime($folder->updated));
			} else {
				$properties = array();
				foreach ($folder->properties as $property) {
					$properties[] = Property::createFromRawObject($property);
				}
				$folder = new Folder($folder->name, $folder->tree, $folder->owner, strtotime($folder->created), strtotime($folder->updated), $properties);
			}
			if ($recursive === true) {
				if ($result->results->subfolders->success !== true) {
					throw new CommunicationException($result->results->subfolders->error->message, $result->results->subfolders->error->code, $result->results->subfolders->error->type);
				}
				if (is_array($result->results->subfolders->data)) {
					foreach ($result->results->subfolders->data as $subfolder) {
						if ($simple === true) {
							$folders[$subfolder->folderId] = new SimpleFolder($subfolder->name, $subfolder->tree, $subfolder->owner, strtotime($subfolder->created),
																			  strtotime($subfolder->updated));
						} else {
							$properties = array();
							foreach ($subfolder->properties as $property) {
								$properties[] = Property::createFromRawObject($property);
							}
							$folders[$subfolder->folderId] = new Folder($subfolder->name, $subfolder->tree, $subfolder->owner, strtotime($subfolder->created), strtotime($subfolder->updated),
																		$properties);
						}
					}
				}
				$folders[$folder->getId()] = $folder;
				if ($simple === false) {
					$folders = Folder::createTree($folders);
					return $folders[$folder->getId()];
				}
				$folder = $folders;
			}
			return $folder;
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