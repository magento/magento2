<?php
/**
 * Wrapper for \Zend_Http_Response class, provides isSuccessful() method
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Outbound
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound\Transport\Http;

class Response
{
    /**
     * @var \Zend_Http_Response $_response
     */
    protected $_response;

    /**
     * @param string $string response from an http request
     */
    public function __construct($string)
    {
        $this->_response = \Zend_Http_Response::fromString($string);
    }


    /**
     * Describes whether response code indicates success
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->_response->isSuccessful();
    }

    /**
     * Gets response status
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_response->getStatus();
    }

    /**
     * Gets response header
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_response->getMessage();
    }

    /**
     * Gets response body
     *
     * This class is just hiding the 'getBody' function since calling that after our curl library has already decoded
     * the body, could cause an error. A perfect example is if the response for our curl call was gzip'ed, curl would
     * have gunzipped it but left the header indicating it was compressed, then \Zend_Http_Response::getBody() would
     * attempt to decompress the raw body, which was already decompressed, causing an error/corruption.
     *
     * @return string
     */
    public function getBody()
    {
        // CURL Doesn't give us access to a truly RAW body, so calling getBody() will fail if Transfer-Encoding is set
        return $this->_response->getRawBody();
    }

    /**
     * Gets response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_response->getHeaders();
    }

    /**
     * Gets a given response header
     *
     * @param string $headerName
     * @return array|null|string
     */
    public function getHeader($headerName)
    {
        return $this->_response->getHeader($headerName);
    }
}
