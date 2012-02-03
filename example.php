<?php
/*
 * @see https://github.com/PhilETaylor/Bf_Google_Safebrowsing
 * @copyright   Copyright (C) 2012 Blue Flame IT Ltd / Phil Taylor, Inc. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE
 */

// Make sure you can get to a version of Zend if needed (if not using full Zend layout)
ini_set('include_path','/Users/phil/Sites/bfnetwork/library/');
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// if doing the above you will need this too
require 'Bf/Google/Safebrowsing.php';

// Enter YOUR api key - not mine!
$apiKey = 'ABQIAAAAPi7bK-oTHk52BUxl1GamKBQhNakko87ip_cnq8qpNla1QjD-7w';

$options = array ('apikey' => $apiKey );
$google = new Bf_Google_Safebrowsing ( $options );

if (FALSE == $google->isListed ( 'http://www.phil-taylor.com' )) {
	echo 'Test Passed<br/>';
} else {
	echo 'Test Failed<br/>';
}

if (TRUE == $google->isListed ( 'http://malware.testing.google.test/testing/malware/' )) {
	echo 'Test Passed<br/>'; // I'm listed!!!
} else {
	echo 'Test Failed<br/>';
}

if ('malware' == $google->getReportedLists()) {
	echo 'Test Passed<br/>'; // I'm listed!!!
} else {
	echo 'Test Failed<br/>';
}



// Test API KEY
try {
	$apiKey = 'NOSUCHKEY';
	$options = array ('apikey' => $apiKey );
	$google = new Bf_Google_Safebrowsing ( $options );
	$google->isListed ( 'http://malware.testing.google.test/testing/malware/' );
	
	// If I get here then...
	echo 'Test Failed1<br/>';

} catch ( Exception $e ) {
	if ($e->getCode () == 400) {
		echo 'Test Passed<br/>';
	} else {
		echo 'Test Failed<br/>';
	}
}