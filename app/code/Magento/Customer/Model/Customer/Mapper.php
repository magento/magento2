<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Convert\ConvertArray;

/**
 * Class Mapper converts Address Service Data Object to an array
 * @since 2.0.0
 */
class Mapper
{
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     * @since 2.0.0
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @since 2.0.0
     */
    public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)
    {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Convert address data object to a flat array
     *
     * @param CustomerInterface $customer
     * @return array
     * @since 2.0.0
     */
    public function toFlatArray(CustomerInterface $customer)
    {
        $flatArray = $this->extensibleDataObjectConverter->toNestedArray($customer, [], \Magento\Customer\Api\Data\CustomerInterface::class);
        unset($flatArray["addresses"]);
        return ConvertArray::toFlatArray($flatArray);
    }
}
