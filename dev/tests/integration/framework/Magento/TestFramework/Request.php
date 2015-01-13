<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * HTTP request implementation that is used instead core one for testing
 */
namespace Magento\TestFramework;

class Request extends \Magento\Framework\App\Request\Http
{
    /**
     * Server super-global mock
     *
     * @var array
     */
    protected $_server = [];

    /**
     * Retrieve HTTP HOST.
     * This method is a stub - all parameters are ignored, just static value returned.
     *
     * @param bool $trimPort
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getHttpHost($trimPort = true)
    {
        return 'localhost';
    }

    /**
     * Set "server" super-global mock
     *
     * @param array $server
     * @return \Magento\TestFramework\Request
     */
    public function setServer(array $server)
    {
        $this->_server = $server;
        return $this;
    }

    /**
     * Overridden getter to avoid using of $_SERVER
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return array|mixed|null
     */
    public function getServer($key = null, $default = null)
    {
        if (null === $key) {
            return $this->_server;
        }

        return isset($this->_server[$key]) ? $this->_server[$key] : $default;
    }

    /**
     * Set the HTTP Method type.
     *
     * Examples are POST, PUT, GET, DELETE
     *
     * @param string $type
     */
    public function setMethod($type)
    {
        $this->_server['REQUEST_METHOD'] = $type;
    }
}
