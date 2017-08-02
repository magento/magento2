<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\HTTP;

/**
 * Factory for HTTP client classes
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class ClientFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.0.0
     */
    protected $_instanceName = null;

    /**
     * Factory constructor (default creates Curl client)
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\HTTP\Client\Curl::class
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return ClientInterface
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
