<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\TestFramework\Helper\Bootstrap;

class TaxRateCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateTaxRateCollectionItem()
    {
        /** @var \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection $collection */
        $collection = Bootstrap::getObjectManager()->get('Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection');
        $dbTaxRatesQty = $collection->count();
        if (($dbTaxRatesQty == 0) || ($collection->getFirstItem()->getId() != 1)) {
            $this->fail("Preconditions failed.");
        }
        /** @var \Magento\Tax\Model\TaxRateCollection $taxRatesCollection */
        $taxRatesCollection = Bootstrap::getObjectManager()
            ->create('Magento\Tax\Model\TaxRateCollection');
        $collectionTaxRatesQty = $taxRatesCollection->count();
        $this->assertEquals($dbTaxRatesQty, $collectionTaxRatesQty, 'Tax rates quantity is invalid.');
        $taxRate = $taxRatesCollection->getFirstItem()->getData();
        $expectedTaxRateData = [
            'code' => 'US-CA-*-Rate 1',
            'tax_calculation_rate_id' => '1',
            'rate' => 8.25,
            'region_name' => 'CA',
            'tax_country_id' => 'US',
            'tax_postcode' => '*',
            'tax_region_id' => '12',
            'titles' => [],
            'zip_is_range' => null,
            'zip_from' => null,
            'zip_to' => null,
        ];
        $this->assertEquals($expectedTaxRateData, $taxRate, 'Tax rate data is invalid.');
    }
}
