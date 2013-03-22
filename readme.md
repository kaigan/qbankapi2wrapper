#QBank - API2 wrapper

## Introduction
QBankAPI2Wrapper is a library that makes it easy to use the QBank API2 directly from PHP. No need to handle the connections or interpreting the results yourself. QBankAPI2Wrapper also provides classes with metods for the most commonly used functions.

## Installation
When you have obtained the source files you need to make them accessible for your scripts. QBankAPI2Wrapper is designed to be put in a place where it is included in php.ini's `include_path`. This is to make it easy to use as a library. It is also possible to place QBankAPI2Wrapper in the same folder as the executing script, although it is not intended.

QBankAPI2Wrapper will try to log to the directory `/var/log/qbankapiwrapper`, so make sure that the folder exists and is writable by the php process.

## Class library
A class library is available at [http://tools.kaigantbk.se/docs/docs/QBankAPI2Wrapper/](http://tools.kaigantbk.se/docs/docs/QBankAPI2Wrapper/) or in PDF form upon request.

## Usage

### Instantiation and login
There are many classes in QBankAPI2Wrapper, but it is quite easy to find the ones that does the calls to QBanks API2. They are named by the convention "QBank_Functionality_API", where _Functionality_ tells what kind of functions the class contains. There are also one class named _QBankAPI_. It is an abstract base class and can therefore not be instantiated. 

To instantiate an API-class it is easiest to use the "QBankAPIFactory" class. After it has been set up. It can instantiate all the other API-classes ready to go.

	<?php
		QBankAPIFactory::setup(QBANK_ADDRESS, USERNAME, PASSWORD);
		try {
			$searchAPI = QBankAPIFactory::createAPI(QBankAPIFactory::SearchAPI);
		} catch (QBankAPIException $qae) {
			// Handle the error
		}
	?>

Please note that this will call `session_start()` and store a value in `$_SESSION`.

#### The manual way
If you want to handle the logins and instantiation of the API-classes yourself it is of course possible to do that.

	<?php
		$searchAPI = new QBankSearchAPI(QBANK_ADDRESS);
		if ($objectAPI->login(USERNAME, PASSWORD) !== true) {
			// Handle faulty login
		} else {
			$qbankSessionHash = $objectAPI->getHash();
			// Save the session hash somewhere
		}
	?>

If there is a need to instantiate several API-classes (there often is), please refrain from logging in when instantiating every class. The best practice is to log in the first time and the recycle the session hash. A log in should only be needed one time per user session.

	<?php
		$searchAPI = new QBankSearchAPI(QBANK_ADDRESS);
		$objectAPI = new QBankObjectAPI(QBANK_ADDRESS);
		if ($objectAPI->login(USERNAME, PASSWORD) !== true) {
			// Handle faulty login
		} else {
			$qbankSessionHash = $objectAPI->getHash();
			$objectAPI->setHash($qbankSessionHash);
			// Save the session hash somewhere
		}
	?>

### Search
Searching is the primary way to retrive more then one object from QBank. There are several ways to conduct a search, and the preffered way to do it depends on the cirumstances.

A search is done by creating a search object. Please note that this class is only available after instantiating the Search-API. It is then possible to customize the search to suit your needs. After the search object is set up, supply it to `QBankSearchAPI::execute()`.

	<?php
		QBankAPIFactory::setup(QBANK_ADDRESS, USERNAME, PASSWORD);
		try {
			$searchAPI = QBankAPIFactory::createAPI(QBankAPIFactory::SearchAPI);
			$search = new Search();
			$search->setFreeText('string to search for');
			$results = $searchAPI->execute($search);
			// Display the results
		} catch (QBankAPIException $qae) {
			// Handle the error
		}
	?>

### Retrieving properties (metadata)
QBank stores properties for every object that the user can set themselves. These are not included in the result by default, but has to be requested.

#### The bad way
An intuitive but bad way to conduct a search is to do a search and then iterate over the results and call `QBankObjectAPI::getObject()` for each result. This will return all information about these objects, but will generate alot of network traffic and overhead. Avoid this!

#### The better way
To achieve the same result as in the bad way without causing a traffic jam on the network the `Search::setAdvancedObjects()` can be set to true. This will batch all the calls together.

#### The smarter way
If there is only some properties that should be returned with the result, the class `PropertyRequest` should be used.

	<?php
		QBankAPIFactory::setup(QBANK_ADDRESS, USERNAME, PASSWORD);
		try {
			$searchAPI = QBankAPIFactory::createAPI(QBankAPIFactory::SearchAPI);
			$search = new Search();
			$properties = array();
			$properties[] = new PropertyRequest(PROPERTY_SYSTEM_NAME);
			$search->addPropertyCriterias($properties);
			$results = $searchAPI->execute($search);
			// Display the results
		} catch (QBankAPIException $qae) {
			// Handle the error
		}
	?>

Please note that this way will produce less information than the previous ones. One change is that a property's default value will be missing. Normally this is ok, but consider what you need and search accordingly.

### The search results
A search returns an object of the type `SearchResult`. This is a container of the `Object`s or `SimpleObject`s that a search will return. It would not be incorrect to see `SearchResult` as an array. It is possible to iterate over and access `SearchResult` as an array.

	<?php
		$results = $searchApi->execute($search);
		$firstResult = $results[0];
		
		foreach($results as $result) {
			echo $result->getId();
		}
	?>

