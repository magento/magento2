<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Amazon.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Rest_Client
 */
#require_once 'Zend/Rest/Client.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Amazon
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon
{
    /**
     * Amazon Web Services Access Key ID
     *
     * @var string
     */
    public $appId;

    /**
     * @var string
     */
    protected $_secretKey = null;

    /**
     * @var string
     */
    protected $_baseUri = null;

    /**
     * List of Amazon Web Service base URLs, indexed by country code
     *
     * @var array
     */
    protected $_baseUriList = array('US' => 'http://webservices.amazon.com',
                                    'UK' => 'http://webservices.amazon.co.uk',
                                    'DE' => 'http://webservices.amazon.de',
                                    'JP' => 'http://webservices.amazon.co.jp',
                                    'FR' => 'http://webservices.amazon.fr',
                                    'CA' => 'http://webservices.amazon.ca');

    /**
     * Reference to REST client object
     *
     * @var Zend_Rest_Client
     */
    protected $_rest = null;


    /**
     * Constructs a new Amazon Web Services Client
     *
     * @param  string $appId       Developer's Amazon appid
     * @param  string $countryCode Country code for Amazon service; may be US, UK, DE, JP, FR, CA
     * @throws Zend_Service_Exception
     * @return Zend_Service_Amazon
     */
    public function __construct($appId, $countryCode = 'US', $secretKey = null)
    {
        $this->appId = (string) $appId;
        $this->_secretKey = $secretKey;

        $countryCode = (string) $countryCode;
        if (!isset($this->_baseUriList[$countryCode])) {
            /**
             * @see Zend_Service_Exception
             */
            #require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception("Unknown country code: $countryCode");
        }

        $this->_baseUri = $this->_baseUriList[$countryCode];
    }


    /**
     * Search for Items
     *
     * @param  array $options Options to use for the Search Query
     * @throws Zend_Service_Exception
     * @return Zend_Service_Amazon_ResultSet
     * @see http://www.amazon.com/gp/aws/sdk/main.html/102-9041115-9057709?s=AWSEcommerceService&v=2005-10-05&p=ApiReference/ItemSearchOperation
     */
    public function itemSearch(array $options)
    {
        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);

        $defaultOptions = array('ResponseGroup' => 'Small');
        $options = $this->_prepareOptions('ItemSearch', $options, $defaultOptions);
        $client->getHttpClient()->resetParameters();
        $response = $client->restGet('/onca/xml', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            #require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('An error occurred sending request. Status code: '
                                           . $response->getStatus());
        }

        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());
        self::_checkErrors($dom);

        /**
         * @see Zend_Service_Amazon_ResultSet
         */
        #require_once 'Zend/Service/Amazon/ResultSet.php';
        return new Zend_Service_Amazon_ResultSet($dom);
    }


    /**
     * Look up item(s) by ASIN
     *
     * @param  string $asin    Amazon ASIN ID
     * @param  array  $options Query Options
     * @see http://www.amazon.com/gp/aws/sdk/main.html/102-9041115-9057709?s=AWSEcommerceService&v=2005-10-05&p=ApiReference/ItemLookupOperation
     * @throws Zend_Service_Exception
     * @return Zend_Service_Amazon_Item|Zend_Service_Amazon_ResultSet
     */
    public function itemLookup($asin, array $options = array())
    {
        $client = $this->getRestClient();
        $client->setUri($this->_baseUri);
        $client->getHttpClient()->resetParameters();

        $defaultOptions = array('ResponseGroup' => 'Small');
        $options['ItemId'] = (string) $asin;
        $options = $this->_prepareOptions('ItemLookup', $options, $defaultOptions);
        $response = $client->restGet('/onca/xml', $options);

        if ($response->isError()) {
            /**
             * @see Zend_Service_Exception
             */
            #require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception(
                'An error occurred sending request. Status code: ' . $response->getStatus()
            );
        }

        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());
        self::_checkErrors($dom);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2005-10-05');
        $items = $xpath->query('//az:Items/az:Item');

        if ($items->length == 1) {
            /**
             * @see Zend_Service_Amazon_Item
             */
            #require_once 'Zend/Service/Amazon/Item.php';
            return new Zend_Service_Amazon_Item($items->item(0));
        }

        /**
         * @see Zend_Service_Amazon_ResultSet
         */
        #require_once 'Zend/Service/Amazon/ResultSet.php';
        return new Zend_Service_Amazon_ResultSet($dom);
    }


    /**
     * Returns a reference to the REST client
     *
     * @return Zend_Rest_Client
     */
    public function getRestClient()
    {
        if($this->_rest === null) {
            $this->_rest = new Zend_Rest_Client();
        }
        return $this->_rest;
    }

    /**
     * Set REST client
     *
     * @param Zend_Rest_Client
     * @return Zend_Service_Amazon
     */
    public function setRestClient(Zend_Rest_Client $client)
    {
        $this->_rest = $client;
        return $this;
    }


    /**
     * Prepare options for request
     *
     * @param  string $query          Action to perform
     * @param  array  $options        User supplied options
     * @param  array  $defaultOptions Default options
     * @return array
     */
    protected function _prepareOptions($query, array $options, array $defaultOptions)
    {
        $options['AWSAccessKeyId'] = $this->appId;
        $options['Service']        = 'AWSECommerceService';
        $options['Operation']      = (string) $query;
        $options['Version']        = '2005-10-05';

        // de-canonicalize out sort key
        if (isset($options['ResponseGroup'])) {
            $responseGroup = explode(',', $options['ResponseGroup']);

            if (!in_array('Request', $responseGroup)) {
                $responseGroup[] = 'Request';
                $options['ResponseGroup'] = implode(',', $responseGroup);
            }
        }

        $options = array_merge($defaultOptions, $options);

        if($this->_secretKey !== null) {
            $options['Timestamp'] = gmdate("Y-m-d\TH:i:s\Z");;
            ksort($options);
            $options['Signature'] = self::computeSignature($this->_baseUri, $this->_secretKey, $options);
        }

        return $options;
    }

    /**
     * Compute Signature for Authentication with Amazon Product Advertising Webservices
     *
     * @param  string $baseUri
     * @param  string $secretKey
     * @param  array $options
     * @return string
     */
    static public function computeSignature($baseUri, $secretKey, array $options)
    {
        #require_once "Zend/Crypt/Hmac.php";

        $signature = self::buildRawSignature($baseUri, $options);
        return base64_encode(
            Zend_Crypt_Hmac::compute($secretKey, 'sha256', $signature, Zend_Crypt_Hmac::BINARY)
        );
    }

    /**
     * Build the Raw Signature Text
     *
     * @param  string $baseUri
     * @param  array $options
     * @return string
     */
    static public function buildRawSignature($baseUri, $options)
    {
        ksort($options);
        $params = array();
        foreach($options AS $k => $v) {
            $params[] = $k."=".rawurlencode($v);
        }

        return sprintf("GET\n%s\n/onca/xml\n%s",
            str_replace('http://', '', $baseUri),
            implode("&", $params)
        );
    }


    /**
     * Check result for errors
     *
     * @param  DOMDocument $dom
     * @throws Zend_Service_Exception
     * @return void
     */
    protected static function _checkErrors(DOMDocument $dom)
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2005-10-05');

        if ($xpath->query('//az:Error')->length >= 1) {
            $code = $xpath->query('//az:Error/az:Code/text()')->item(0)->data;
            $message = $xpath->query('//az:Error/az:Message/text()')->item(0)->data;

            switch($code) {
                case 'AWS.ECommerceService.NoExactMatches':
                    break;
                default:
                    /**
                     * @see Zend_Service_Exception
                     */
                    #require_once 'Zend/Service/Exception.php';
                    throw new Zend_Service_Exception("$message ($code)");
            }
        }
    }
}
