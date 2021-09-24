<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for customer addresses collection
 */
namespace Magento\Customer\Model\ResourceModel\Address;

use Magento\Store\Model\ScopeInterface;

class StoreAddressCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testSetCustomerFilter()
    {
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Address\StoreAddressCollection::class
        );
        $select = $collection->getSelect();
        $this->assertSame($collection, $collection->setCustomerFilter([1, 2]));
        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Customer::class
        );
        $collection->setCustomerFilter($customer);
        $customer->setId(3);
        $collection->setCustomerFilter($customer);
        $this->assertStringMatchesFormat(
            '%AWHERE%S(%Sparent_id%S IN(%S1%S, %S2%S))%SAND%S(%Sparent_id%S = %S-1%S)%SAND%S(%Sparent_id%S = %S3%S)%A',
            (string)$select
        );
        $allowedCountriesObj = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Directory\Model\AllowedCountries::class
        );
        $storeId = $customer->getStoreId();
        $allowedCountries = $allowedCountriesObj->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);
        $strAllowedCountries = implode("%S, %S", $allowedCountries);
        $this->assertStringMatchesFormat('%AWHERE%S(%Scountry_id%S IN(%S' . $strAllowedCountries . '%S))%A', (string)$select);
    }
}
