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

    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(Customer $customer)
    {
        return $this->objectManager->create(CustomerDataGetter::class, ['customer' => $customer]);
    }
}