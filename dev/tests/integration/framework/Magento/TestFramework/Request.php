<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_server = array();

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
