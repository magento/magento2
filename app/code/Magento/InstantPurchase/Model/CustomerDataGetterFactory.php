<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Model\Customer;
use Magento\Framework\ObjectManager\ObjectManager;

class CustomerDataGetterFactory
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * CustomerDataGetterFactory constructor.
     * @param ObjectManager $objectManager
     */
    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Customer $customer
     * @return CustomerDataGetter
     */
    public function create(Customer $customer): CustomerDataGetter
    {
        return $this->objectManager->create(CustomerDataGetter::class, ['customer' => $customer]);
    }
}
