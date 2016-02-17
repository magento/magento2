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
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_Amazon_Abstract
 */
#require_once 'Zend/Service/Amazon/Abstract.php';

/**
 * @see Zend_Service_Amazon_Ec2_Response
 */
#require_once 'Zend/Service/Amazon/Ec2/Response.php';

/**
 * @see Zend_Service_Amazon_Ec2_Exception
 */
#require_once 'Zend/Service/Amazon/Ec2/Exception.php';

/**
 * Provides the basic functionality to send a request to the Amazon Ec2 Query API
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_Amazon_Ec2_Abstract extends Zend_Service_Amazon_Abstract
{
    /**
     * The HTTP query server
     */
    protected $_ec2Endpoint = 'ec2.amazonaws.com';

    /**
     * The API version to use
     */
    protected $_ec2ApiVersion = '2009-04-04';

    /**
     * Signature Version
     */
    protected $_ec2SignatureVersion = '2';

    /**
     * Signature Encoding Method
     */
    protected $_ec2SignatureMethod = 'HmacSHA256';

    /**
     * Period after which HTTP request will timeout in seconds
     */
    protected $_httpTimeout = 10;

    /**
     * @var string Amazon Region
     */
    protected static $_defaultRegion = null;

    /**
     * @var string Amazon Region
     */
    protected $_region;

    /**
     * An array that contains all the valid Amazon Ec2 Regions.
     *
     * @var array
     */
    protected static $_validEc2Regions = array('eu-west-1', 'us-east-1');

    /**
     * Create Amazon client.
     *
     * @param  string $access_key       Override the default Access Key
     * @param  string $secret_key       Override the default Secret Key
     * @param  string $region           Sets the AWS Region
     * @return void
     */
    public function __construct($accessKey=null, $secretKey=null, $region=null)
    {
        if(!$region) {
            $region = self::$_defaultRegion;
        } else {
            // make rue the region is valid
            if(!empty($region) && !in_array(strtolower($region), self::$_validEc2Regions, true)) {
                #require_once 'Zend/Service/Amazon/Exception.php';
                throw new Zend_Service_Amazon_Exception('Invalid Amazon Ec2 Region');
            }
        }

        $this->_region = $region;

        parent::__construct($accessKey, $secretKey);
    }

    /**
     * Set which region you are working in.  It will append the
     * end point automaticly
     *
     * @param string $region
     */
    public static function setRegion($region)
    {
        if(in_array(strtolower($region), self::$_validEc2Regions, true)) {
            self::$_defaultRegion = $region;
        } else {
            #require_once 'Zend/Service/Amazon/Exception.php';
            throw new Zend_Service_Amazon_Exception('Invalid Amazon Ec2 Region');
        }
    }

    /**
     * Method to fetch the AWS Region
     *
     * @return string
     */
    protected function _getRegion()
    {
        return (!empty($this->_region)) ? $this->_region . '.' : '';
    }

    /**
     * Sends a HTTP request to the queue service using Zend_Http_Client
     *
     * @param  array $params List of parameters to send with the request
     * @return Zend_Service_Amazon_Ec2_Response
     * @throws Zend_Service_Amazon_Ec2_Exception
     */
    protected function sendRequest(array $params = array())
    {
        $url = 'https://' . $this->_getRegion() . $this->_ec2Endpoint . '/';

        $params = $this->addRequiredParameters($params);

        try {
            /* @var $request Zend_Http_Client */
            $request = self::getHttpClient();
            $request->resetParameters();

            $request->setConfig(array(
                'timeout' => $this->_httpTimeout
            ));

            $request->setUri($url);
            $request->setMethod(Zend_Http_Client::POST);
            $request->setParameterPost($params);

            $httpResponse = $request->request();


        } catch (Zend_Http_Client_Exception $zhce) {
            $message = 'Error in request to AWS service: ' . $zhce->getMessage();
            throw new Zend_Service_Amazon_Ec2_Exception($message, $zhce->getCode(), $zhce);
        }
        $response = new Zend_Service_Amazon_Ec2_Response($httpResponse);
        $this->checkForErrors($response);

        return $response;
    }

    /**
     * Adds required authentication and version parameters to an array of
     * parameters
     *
     * The required parameters are:
     * - AWSAccessKey
     * - SignatureVersion
     * - Timestamp
     * - Version and
     * - Signature
     *
     * If a required parameter is already set in the <tt>$parameters</tt> array,
     * it is overwritten.
     *
     * @param array $parameters the array to which to add the required
     *                          parameters.
     *
     * @return array
     */
    protected function addRequiredParameters(array $parameters)
    {
        $parameters['AWSAccessKeyId']   = $this->_getAccessKey();
        $parameters['SignatureVersion'] = $this->_ec2SignatureVersion;
        $parameters['Timestamp']        = gmdate('Y-m-d\TH:i:s\Z');
        $parameters['Version']          = $this->_ec2ApiVersion;
        $parameters['SignatureMethod']  = $this->_ec2SignatureMethod;
        $parameters['Signature']        = $this->signParameters($parameters);

        return $parameters;
    }

    /**
     * Computes the RFC 2104-compliant HMAC signature for request parameters
     *
     * This implements the Amazon Web Services signature, as per the following
     * specification:
     *
     * 1. Sort all request parameters (including <tt>SignatureVersion</tt> and
     *    excluding <tt>Signature</tt>, the value of which is being created),
     *    ignoring case.
     *
     * 2. Iterate over the sorted list and append the parameter name (in its
     *    original case) and then its value. Do not URL-encode the parameter
     *    values before constructing this string. Do not use any separator
     *    characters when appending strings.
     *
     * @param array  $parameters the parameters for which to get the signature.
     * @param string $secretKey  the secret key to use to sign the parameters.
     *
     * @return string the signed data.
     */
    protected function signParameters(array $paramaters)
    {
        $data = "POST\n";
        $data .= $this->_getRegion() . $this->_ec2Endpoint . "\n";
        $data .= "/\n";

        uksort($paramaters, 'strcmp');
        unset($paramaters['Signature']);

        $arrData = array();
        foreach($paramaters as $key => $value) {
            $arrData[] = $key . '=' . str_replace("%7E", "~", rawurlencode($value));
        }

        $data .= implode('&', $arrData);

        #require_once 'Zend/Crypt/Hmac.php';
        $hmac = Zend_Crypt_Hmac::compute($this->_getSecretKey(), 'SHA256', $data, Zend_Crypt_Hmac::BINARY);

        return base64_encode($hmac);
    }

    /**
     * Checks for errors responses from Amazon
     *
     * @param Zend_Service_Amazon_Ec2_Response $response the response object to
     *                                                   check.
     *
     * @return void
     *
     * @throws Zend_Service_Amazon_Ec2_Exception if one or more errors are
     *         returned from Amazon.
     */
    private function checkForErrors(Zend_Service_Amazon_Ec2_Response $response)
    {
        $xpath = new DOMXPath($response->getDocument());
        $list  = $xpath->query('//Error');
        if ($list->length > 0) {
            $node    = $list->item(0);
            $code    = $xpath->evaluate('string(Code/text())', $node);
            $message = $xpath->evaluate('string(Message/text())', $node);
            throw new Zend_Service_Amazon_Ec2_Exception($message, 0, $code);
        }

    }
}
