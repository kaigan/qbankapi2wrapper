<?php
function qbankwrapperautoload($class) {
	$path = dirname(__FILE__).'/';
	$subPaths = array('', 'model/', 'exceptions/');
	foreach ($subPaths as $subPath) {
		$file = $path.$subPath.$class.'.php';
		if (file_exists($file)) {
			require_once $file;
			break;
		}
	}
}
spl_autoload_register('qbankwrapperautoload', true);
?>