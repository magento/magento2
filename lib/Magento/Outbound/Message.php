<?php
/**
 * Message that can be sent to endpoints.
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
namespace Magento\Outbound;

class Message implements \Magento\Outbound\MessageInterface
{
    /** default timeout value in seconds */
    const DEFAULT_TIMEOUT = 20;

    /**
     * @var array
     */
    protected $_headers = array();

    /**
     * @var string|null
     */
    protected $_body;

    /**
     * @var int
     */
    protected $_timeout;

    /** @var string */
    protected $_endpointUrl;


    /**
     * @param string $endpointUrl
     * @param array $headers
     * @param null $body
     * @param int $timeout in seconds
     */
    public function __construct($endpointUrl, $headers = array(), $body = null, $timeout = self::DEFAULT_TIMEOUT)
    {
        $this->_endpointUrl = $endpointUrl;
        $this->_headers = $headers;
        $this->_body = $body;
        $this->_timeout = $timeout;
    }

    /**
     * return endpoint url
     *
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->_endpointUrl;
    }

    /**
     * Return headers array
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * return body
     *
     * @return string|null
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * return timeout in seconds
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }
}
