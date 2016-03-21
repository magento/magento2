<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerManagementInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class CustomerManagement implements CustomerManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $customersFactory;

    /**
     * @param CollectionFactory $customersFactory
     */
    public function __construct(CollectionFactory $customersFactory)
    {
        $this->customersFactory = $customersFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $customers = $this->customersFactory->create();
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customers */
        return $customers->getSize();
    }
}
