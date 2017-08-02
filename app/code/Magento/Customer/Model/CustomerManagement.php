<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerManagementInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

/**
 * Class \Magento\Customer\Model\CustomerManagement
 *
 * @since 2.0.0
 */
class CustomerManagement implements CustomerManagementInterface
{
    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $customersFactory;

    /**
     * @param CollectionFactory $customersFactory
     * @since 2.0.0
     */
    public function __construct(CollectionFactory $customersFactory)
    {
        $this->customersFactory = $customersFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCount()
    {
        $customers = $this->customersFactory->create();
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customers */
        return $customers->getSize();
    }
}
