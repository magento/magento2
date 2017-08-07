<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order;

/**
 * Class CollectionFactory
 * @since 2.1.1
 */
class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.1.1
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.1.1
     */
    private $instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.1.1
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Sales\Model\ResourceModel\Order\Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.1
     */
    public function create($customerId = null)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $collection */
        $collection = $this->objectManager->create($this->instanceName);

        if ($customerId) {
            $collection->addFieldToFilter('customer_id', $customerId);
        }

        return $collection;
    }
}
