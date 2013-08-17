<?php
/**
 * Wrapper for Zend_Http_Response class, provides isSuccessful() method
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Outbound_Transport_Http_Response
{
    /**
     * @var Zend_Http_Response
     */
    protected $_response;

    public function __construct(Zend_Http_Response $response)
    {
        $this->_response = $response;
    }


    /**
     * Describes whether response code indicates success
     *
     * @return bool
     */
    public function isSuccessful()
    {
        $statusCode = $this->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        }
        return false;
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
     * @return string
     */
    public function getBody()
    {
        // CURL Doesn't give us access to a truly RAW body, so calling getBody() will fail if Transfer-Encoding is set
        return $this->_response->getRawBody();
    }

    /**
     * Gets response body
     *
     * @return string
     */
    public function getRawBody()
    {
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
