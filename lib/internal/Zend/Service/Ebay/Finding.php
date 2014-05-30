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
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Finding.php 22824 2010-08-09 18:59:54Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Abstract
 */
#require_once 'Zend/Service/Ebay/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Abstract
 */
class Zend_Service_Ebay_Finding extends Zend_Service_Ebay_Abstract
{
    const SERVICE_NAME         = 'FindingService';
    const SERVICE_VERSION      = '1.0.0';
    const RESPONSE_DATA_FORMAT = 'XML';

    const ENDPOINT_URI  = 'http://svcs.ebay.com';
    const ENDPOINT_PATH = 'services/search/FindingService/v1';

    const XMLNS_FINDING = 'e';
    const XMLNS_MS      = 'ms';

    /**
     * @var array
     */
    protected static $_xmlNamespaces = array(
        self::XMLNS_FINDING => 'http://www.ebay.com/marketplace/search/v1/services',
        self::XMLNS_MS      => 'http://www.ebay.com/marketplace/services'
    );

    /**
     *
     * @var array
     */
    protected $_options = array(
        self::OPTION_GLOBAL_ID => 'EBAY-US'
    );

    /**
     * @return array
     */
    public static function getXmlNamespaces()
    {
        return self::$_xmlNamespaces;
    }

    /**
     * @param  Zend_Config|array|string $options Application Id or array of options
     * @throws Zend_Service_Ebay_Finding_Exception When application id is missing
     * @return void
     */
    public function __construct($options)
    {
        // prepare options
        if (is_string($options)) {
            // application id was given
            $options = array(self::OPTION_APP_ID => $options);
        } else {
            // check application id
            $options = parent::optionsToArray($options);
            if (!array_key_exists(self::OPTION_APP_ID, $options)) {
                /**
                 * @see Zend_Service_Ebay_Finding_Exception
                 */
                #require_once 'Zend/Service/Ebay/Finding/Exception.php';
                throw new Zend_Service_Ebay_Finding_Exception(
                    'Application Id is missing.');
            }
        }

        // load options
        parent::setOption($options);
    }

    /**
     * @param  Zend_Rest_Client $client
     * @return Zend_Service_Ebay_Finding Provides a fluent interface
     */
    public function setClient($client)
    {
        if (!$client instanceof Zend_Rest_Client) {
            /**
             * @see Zend_Service_Ebay_Finding_Exception
             */
            #require_once 'Zend/Service/Ebay/Finding/Exception.php';
            throw new Zend_Service_Ebay_Finding_Exception(
                'Client object must extend Zend_Rest_Client.');
        }
        $this->_client = $client;

        return $this;
    }

    /**
     * @return Zend_Rest_Client
     */
    public function getClient()
    {
        if (!$this->_client instanceof Zend_Rest_Client) {
            /**
             * @see Zend_Rest_Client
             */
            #require_once 'Zend/Rest/Client.php';
            $this->_client = new Zend_Rest_Client();
        }
        return $this->_client;
    }

    /**
     * Finds items by a keyword query and/or category and allows searching
     * within item descriptions.
     *
     * @param  string            $keywords
     * @param  boolean           $descriptionSearch
     * @param  integer           $categoryId
     * @param  Zend_Config|array $options
     * @link   http://developer.ebay.com/DevZone/finding/CallRef/findItemsAdvanced.html
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItemsAdvanced($keywords, $descriptionSearch = true, $categoryId = null, $options = null)
    {
        // prepare options
        $options                      = parent::optionsToArray($options);
        $options['keywords']          = $keywords;
        $options['descriptionSearch'] = $descriptionSearch;
        if (!empty($categoryId)) {
            $options['categoryId'] = $categoryId;
        }

        // do request
        return $this->_findItems($options, 'findItemsAdvanced');
    }

    /**
     * Finds items in a specific category. Results can be filtered and sorted.
     *
     * @param  integer           $categoryId
     * @param  Zend_Config|array $options
     * @link   http://developer.ebay.com/DevZone/finding/CallRef/findItemsByCategory.html
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItemsByCategory($categoryId, $options = null)
    {
        // prepare options
        $options               = parent::optionsToArray($options);
        $options['categoryId'] = $categoryId;

        // do request
        return $this->_findItems($options, 'findItemsByCategory');
    }

    /**
     * Finds items on eBay based upon a keyword query and returns details for
     * matching items.
     *
     * @param  string            $keywords
     * @param  Zend_Config|array $options
     * @link   http://developer.ebay.com/DevZone/finding/CallRef/findItemsByKeywords.html
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItemsByKeywords($keywords, $options = null)
    {
        // prepare options
        $options             = parent::optionsToArray($options);
        $options['keywords'] = $keywords;

        // do request
        return $this->_findItems($options, 'findItemsByKeywords');
    }

    /**
     * Finds items based upon a product ID, such as an ISBN, UPC, EAN, or ePID.
     *
     * @param  integer           $productId
     * @param  string            $productIdType Default value is ReferenceID
     * @param  Zend_Config|array $options
     * @link   http://developer.ebay.com/DevZone/finding/CallRef/findItemsByProduct.html
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItemsByProduct($productId, $productIdType = null, $options = null)
    {
        if (null == $productIdType) {
            $productIdType = 'ReferenceID';
        }

        // prepare options
        $options              = parent::optionsToArray($options);
        $options['productId'] = array(''     => $productId,
                                      'type' => $productIdType);

        // do request
        return $this->_findItems($options, 'findItemsByProduct');
    }

    /**
     * Finds items in eBay stores. Can search a specific store or can search all
     * stores with a keyword query.
     *
     * @param  string            $storeName
     * @param  Zend_Config|array $options
     * @link   http://developer.ebay.com/DevZone/finding/CallRef/findItemsIneBayStores.html
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItemsInEbayStores($storeName, $options = null)
    {
        // prepare options
        $options              = parent::optionsToArray($options);
        $options['storeName'] = $storeName;

        // do request
        return $this->_findItems($options, 'findItemsIneBayStores');
    }

    /**
     * @param  array  $options
     * @param  string $operation
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    protected function _findItems(array $options, $operation)
    {
        // set default output selector value
        if (!array_key_exists('outputSelector', $options)) {
            $options['outputSelector'] = array('AspectHistogram',
                                               'CategoryHistogram',
                                               'SellerInfo',
                                               'StoreInfo');
        }

        // do request
        $dom = $this->_request($operation, $options);

        /**
         * @see Zend_Service_Ebay_Finding_Response_Items
         */
        #require_once 'Zend/Service/Ebay/Finding/Response/Items.php';
        $response = new Zend_Service_Ebay_Finding_Response_Items($dom->firstChild);
        return $response->setOperation($operation)
                        ->setOption($options);
    }

    /**
     * Gets category and/or aspect metadata for the specified category.
     *
     * @param  integer           $categoryId
     * @param  Zend_Config|array $options
     * @link   http://developer.ebay.com/DevZone/finding/CallRef/getHistograms.html
     * @return Zend_Service_Ebay_Finding_Response_Histograms
     */
    public function getHistograms($categoryId, $options = null)
    {
        // prepare options
        $options               = parent::optionsToArray($options);
        $options['categoryId'] = $categoryId;

        // do request
        $operation = 'getHistograms';
        $dom       = $this->_request($operation, $options);

        /**
         * @see Zend_Service_Ebay_Finding_Response_Histograms
         */
        #require_once 'Zend/Service/Ebay/Finding/Response/Histograms.php';
        $response = new Zend_Service_Ebay_Finding_Response_Histograms($dom->firstChild);
        return $response->setOperation($operation)
                        ->setOption($options);
    }

    /**
     * Checks specified keywords and returns correctly spelled keywords for best
     * search results.
     *
     * @param  string            $keywords
     * @param  Zend_Config|array $options
     * @link   http://developer.ebay.com/DevZone/finding/CallRef/getSearchKeywordsRecommendation.html
     * @return Zend_Service_Ebay_Finding_Response_Keywords
     */
    public function getSearchKeywordsRecommendation($keywords, $options = null)
    {
        // prepare options
        $options             = parent::optionsToArray($options);
        $options['keywords'] = $keywords;

        // do request
        $operation = 'getSearchKeywordsRecommendation';
        $dom       = $this->_request($operation, $options);

        /**
         * @see Zend_Service_Ebay_Finding_Response_Keywords
         */
        #require_once 'Zend/Service/Ebay/Finding/Response/Keywords.php';
        $response = new Zend_Service_Ebay_Finding_Response_Keywords($dom->firstChild);
        return $response->setOperation($operation)
                        ->setOption($options);
    }

    /**
     * @param  string $operation
     * @param  array  $options
     * @link   http://developer.ebay.com/DevZone/finding/Concepts/MakingACall.html#StandardURLParameters
     * @return DOMDocument
     */
    protected function _request($operation, array $options = null)
    {
        // generate default options
        // constructor load global-id and application-id values
        $default = array('OPERATION-NAME'       => $operation,
                         'SERVICE-NAME'         => self::SERVICE_NAME,
                         'SERVICE-VERSION'      => self::SERVICE_VERSION,
                         'GLOBAL-ID'            => $this->getOption(self::OPTION_GLOBAL_ID),
                         'SECURITY-APPNAME'     => $this->getOption(self::OPTION_APP_ID),
                         'RESPONSE-DATA-FORMAT' => self::RESPONSE_DATA_FORMAT,
                         'REST-PAYLOAD'         => '');

        // prepare options to ebay syntax
        $options = $default + $this->_optionsToNameValueSyntax($options);

        // do request
        $client = $this->getClient();
        $client->getHttpClient()->resetParameters();
        $response = $client->setUri(self::ENDPOINT_URI)
                           ->restGet(self::ENDPOINT_PATH, $options);

        return $this->_parseResponse($response);
    }

    /**
     * Search for error from request.
     *
     * If any error is found a DOMDocument is returned, this object contains a
     * DOMXPath object as "ebayFindingXPath" attribute.
     *
     * @param  Zend_Http_Response $response
     * @link   http://developer.ebay.com/DevZone/finding/CallRef/types/ErrorSeverity.html
     * @see    Zend_Service_Ebay_Finding_Abstract::_initXPath()
     * @throws Zend_Service_Ebay_Finding_Exception When any error occurrs during request
     * @return DOMDocument
     */
    protected function _parseResponse(Zend_Http_Response $response)
    {
        // error message
        $message = '';

        // first trying, loading XML
        $dom = new DOMDocument();
        if (!@$dom->loadXML($response->getBody())) {
            $message = 'It was not possible to load XML returned.';
        }

        // second trying, check request status
        if ($response->isError()) {
            $message = $response->getMessage()
                     . ' (HTTP status code #' . $response->getStatus() . ')';
        }

        // third trying, search for error message into XML response
        // only first error that contains severiry=Error is read
        $xpath = new DOMXPath($dom);
        foreach (self::$_xmlNamespaces as $alias => $uri) {
            $xpath->registerNamespace($alias, $uri);
        }
        $ns           = self::XMLNS_FINDING;
        $nsMs         = self::XMLNS_MS;
        $expression   = "//$nsMs:errorMessage[1]/$ns:error/$ns:severity[.='Error']";
        $severityNode = $xpath->query($expression)->item(0);
        if ($severityNode) {
            $errorNode = $severityNode->parentNode;
            // ebay message
            $messageNode = $xpath->query("//$ns:message[1]", $errorNode)->item(0);
            if ($messageNode) {
                $message = 'eBay error: ' . $messageNode->nodeValue;
            } else {
                $message = 'eBay error: unknown';
            }
            // ebay error id
            $errorIdNode = $xpath->query("//$ns:errorId[1]", $errorNode)->item(0);
            if ($errorIdNode) {
                $message .= ' (#' . $errorIdNode->nodeValue . ')';
            }
        }

        // throw exception when an error was detected
        if (strlen($message) > 0) {
            /**
             * @see Zend_Service_Ebay_Finding_Exception
             */
            #require_once 'Zend/Service/Ebay/Finding/Exception.php';
            throw new Zend_Service_Ebay_Finding_Exception($message);
        }

        // add xpath to dom document
        // it allows service_ebay_finding classes use this
        $dom->ebayFindingXPath = $xpath;

        return $dom;
    }
}
