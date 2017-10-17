<?php

namespace Magento\OneTouchOrdering\Model;

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