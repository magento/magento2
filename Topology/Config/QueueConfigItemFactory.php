<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\Queue\Config\QueueConfigItem
 * @since 2.2.0
 */
class QueueConfigItemFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.2.0
     */
    private $instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = QueueConfigItem::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return QueueConfigItem
     * @since 2.2.0
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
