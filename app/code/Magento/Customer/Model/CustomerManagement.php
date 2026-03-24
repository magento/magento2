<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
