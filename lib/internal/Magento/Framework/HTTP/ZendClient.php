<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento HTTP Client
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\HTTP;

class ZendClient extends \Zend_Http_Client
{
    /**
     * Internal flag to allow decoding of request body
     *
     * @var bool
     */
    protected $_urlEncodeBody = true;

    /**
     * @param null|\Zend_Uri_Http|string $uri
     * @param null|array $config
     */
    public function __construct($uri = null, $config = null)
    {
        $this->config['useragent'] = 'Magento\Framework\HTTP\ZendClient';

        parent::__construct($uri, $config);
    }

    /**
     * @return $this
     */
    protected function _trySetCurlAdapter()
    {
        if (extension_loaded('curl')) {
            $this->setAdapter(new \Magento\Framework\HTTP\Adapter\Curl());
        }
        return $this;
    }

    /**
     * @param null|string $method
     * @return \Zend_Http_Response
     */
    public function request($method = null)
    {
        $this->_trySetCurlAdapter();
        return parent::request($method);
    }

    /**
     * Change value of internal flag to disable/enable custom prepare functionality
     *
     * @param bool $flag
     * @return \Magento\Framework\HTTP\ZendClient
     */
    public function setUrlEncodeBody($flag)
    {
        $this->_urlEncodeBody = $flag;
        return $this;
    }

    /**
     * Adding custom functionality to decode data after
     * standard prepare functionality
     *
     * @return string
     */
    protected function _prepareBody()
    {
        $body = parent::_prepareBody();

        if (!$this->_urlEncodeBody && $body) {
            $body = urldecode($body);
            $this->setHeaders('Content-length', strlen($body));
        }

        return $body;
    }
}
