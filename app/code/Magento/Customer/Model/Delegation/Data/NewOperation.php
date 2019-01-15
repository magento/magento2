<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Delegation\Data;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Data required for delegated new-account operation.
 */
class NewOperation
{
    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var array
     */
    private $additionalData;

    /**
     * @param CustomerInterface $customer
     * @param array $additionalData
     */
    public function __construct(
        CustomerInterface $customer,
        array $additionalData
    ) {
        $this->customer = $customer;
        $this->additionalData = $additionalData;
    }

    /**
     * @return CustomerInterface
     */
    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
