<?php
/**
 * @package Blue Flame Framework (bfFramework)
 * @copyright Copyright (C) 2011 Blue Flame IT Ltd. All rights reserved.
 * @license GNU General Public License
 * @link http://www.blueflameit.ltd.uk
 * @author Phil Taylor / Blue Flame IT Ltd.
 *
 * bfFramework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * bfFramework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this package.  If not, see http://www.gnu.org/licenses/
 */
class Bf_Google_Safebrowsing {
	
	/**
	 * The GET Endpoint
	 * 
	 * @var string
	 */
	private $_ggGETEntpoint = 'https://sb-ssl.google.com/safebrowsing/api/lookup?client=%s&apikey=%s&appver=%s&pver=%s&url=%s';
	
	/**
	 * The API key to use in requests
	 *
	 * @var string The API key
	 */
	protected $_apiKey;
	
	/**
	 * The App Version
	 *
	 * @var string The Version String
	 */
	protected $_apiAppver = '0.0.1';
	
	/**
	 * The Client Identifier
	 *
	 * @var string The Version String
	 */
	protected $_apiClient = 'Bf_Google_Safebrowsing';
	
	/**
	 * The Url To Check
	 *
	 * @var string Url
	 */
	protected $_apiUrl = '';
	
	/**
	 * The Google pver 
	 * @see http://code.google.com/apis/safebrowsing/lookup_guide.html
	 *
	 * @var string pver
	 */
	protected $_apiPver = '3.0';
	
	/**
	 * The HTTP Client object to use to perform requests
	 *
	 * @var Zend_Http_Client
	 */
	protected $_httpclient;
	
	public function __construct($options = null) {
		
		if ($options instanceof Zend_Config) {
			$options = $options->toArray ();
		}
		
		if (is_array ( $options )) {
			$this->setOptions ( $options );
		}
	}
	
	/**
	 * Set the apikey to use - obtain one here: 
	 * @url http://code.google.com/apis/safebrowsing/key_signup.html
	 * @param string $apiKey Google API Key
	 */
	public function setApikey($apiKey) {
		$this->_apiKey = $apiKey;
	}
	
	/**
	 * Set options
	 * One or more of username, password, soapClient
	 *
	 * @param  array $options
	 * @return Bf_Google_Safebrowsing
	 */
	public function setOptions(array $options) {
		foreach ( $options as $key => $value ) {
			$method = 'set' . $key;
			if (method_exists ( $this, $method )) {
				$this->$method ( $value );
			}
		}
		
		return $this;
	}
	
	/**
	 * Sets the Zend_Http_Client object to use in requests. If not provided a default will
	 * be used.
	 *
	 * @param Zend_Http_Client $client The HTTP client instance to use
	 * @return Bf_Google_Safebrowsing
	 */
	public function setHttpClient(Zend_Http_Client $client) {
		$this->_httpclient = $client;
		return $this;
	}
	
	/**
	 * Returns the instance of the Zend_Http_Client which will be used. Creates an instance
	 * of Zend_Http_Client if no previous client was set.
	 *
	 * @return Zend_Http_Client The HTTP client which will be used
	 */
	public function getHttpClient() {
		
		if (! ($this->_httpclient instanceof Zend_Http_Client)) {
			$client = new Zend_Http_Client ();
			$client->setConfig ( array ('maxredirects' => 2, 'timeout' => 5 ) );
			
			$this->setHttpClient ( $client );
		}
		
		$this->_httpclient->resetParameters ();
		return $this->_httpclient;
	}
	
	private function _buildUri($uri) {
		return sprintf ( $this->_ggGETEntpoint, $this->_apiClient, $this->_apiKey, $this->_apiAppver, $this->_apiPver, urlencode ( $uri ) );
	}
	
	public function isListed($uri) {
		
		$googleHttp = $this->getHttpClient ();
		$uri = $this->_buildUri ( $uri );
		$googleHttp->setUri ( $uri );
		$response = $googleHttp->request ( 'GET' );
		
		switch ($googleHttp->getLastResponse ()->getStatus ()) {
			case 204 :
				$listed = FALSE;
				break;
			case 200 :
				$listed = TRUE;
				break;
			case 400 :
				throw new Exception ( 'Bad Request Ñ The HTTP request was not correctly formed.', 400 );
				break;
			case 401 :
				throw new Exception ( 'Not Authorized Ñ The apikey is not authorized', 401 );
				break;
			case 503 :
				throw new Exception ( ' Service Unavailable Ñ The server cannot handle the request. Besides the normal server failures, it could also indicate that the client has been ÒthrottledÓ by sending too many requests', 503 );
				break;
			default :
				throw new Exception ( $response->getBody (), 400 );
				break;
		}
		
		return $listed;
	}
	
	public function getReportedLists() {
		return $this->getHttpClient ()->getLastResponse ()->getBody ();
	}
}