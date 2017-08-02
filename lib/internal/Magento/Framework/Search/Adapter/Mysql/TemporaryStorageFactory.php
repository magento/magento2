<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\ObjectManagerInterface;

/**
 * TemporaryStorage Factory
 *
 * @codeCoverageIgnore
 * @api
 * @since 2.0.0
 */
class TemporaryStorageFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.0.0
     */
    protected $instanceName = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
