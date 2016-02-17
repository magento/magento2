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
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Http_Response
 */
#require_once 'Zend/Http/Response.php';

/**
 * @see Zend_Json
 */
#require_once 'Zend/Json.php';

/**
 * Representation of a response from Twitter.
 *
 * Provides:
 *
 * - method for testing if we have a successful call
 * - method for retrieving errors, if any
 * - method for retrieving the raw JSON
 * - method for retrieving the decoded response
 * - proxying to elements of the decoded response via property overloading
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Twitter_Response
{
    /**
     * @var Zend_Http_Response
     */
    protected $httpResponse;

    /**
     * @var array|stdClass
     */
    protected $jsonBody;

    /**
     * @var string
     */
    protected $rawBody;

    /**
     * Constructor
     *
     * Assigns the HTTP response to a property, as well as the body
     * representation. It then attempts to decode the body as JSON.
     *
     * @param  Zend_Http_Response $httpResponse
     * @throws Zend_Service_Twitter_Exception if unable to decode JSON response
     */
    public function __construct(Zend_Http_Response $httpResponse)
    {
        $this->httpResponse = $httpResponse;
        $this->rawBody      = $httpResponse->getBody();
        try {
            $jsonBody = Zend_Json::decode($this->rawBody, Zend_Json::TYPE_OBJECT);
            $this->jsonBody = $jsonBody;
        } catch (Zend_Json_Exception $e) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(sprintf(
                'Unable to decode response from twitter: %s',
                $e->getMessage()
            ), 0, $e);
        }
    }

    /**
     * Property overloading to JSON elements
     *
     * If a named property exists within the JSON response returned,
     * proxies to it. Otherwise, returns null.
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (null === $this->jsonBody) {
            return null;
        }
        if (!isset($this->jsonBody->{$name})) {
            return null;
        }
        return $this->jsonBody->{$name};
    }

    /**
     * Was the request successful?
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->httpResponse->isSuccessful();
    }

    /**
     * Did an error occur in the request?
     *
     * @return bool
     */
    public function isError()
    {
        return !$this->httpResponse->isSuccessful();
    }

    /**
     * Retrieve the errors.
     *
     * Twitter _should_ return a standard error object, which contains an
     * "errors" property pointing to an array of errors. This method will
     * return that array if present, and raise an exception if not detected.
     *
     * If the response was successful, an empty array is returned.
     *
     * @return array
     * @throws Exception\DomainException if unable to detect structure of error response
     */
    public function getErrors()
    {
        if (!$this->isError()) {
            return array();
        }
        if (null === $this->jsonBody
            || !isset($this->jsonBody->errors)
        ) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Either no JSON response received, or JSON error response is malformed; cannot return errors'
            );
        }
        return $this->jsonBody->errors;
    }

    /**
     * Retrieve the raw response body
     *
     * @return string
     */
    public function getRawResponse()
    {
        return $this->rawBody;
    }

    /**
     * Retun the decoded response body
     *
     * @return array|stdClass
     */
    public function toValue()
    {
        return $this->jsonBody;
    }
}
