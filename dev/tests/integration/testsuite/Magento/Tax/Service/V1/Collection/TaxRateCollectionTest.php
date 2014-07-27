<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Service\V1\Collection;

use Magento\TestFramework\Helper\Bootstrap;

class TaxRateCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateTaxRateCollectionItem()
    {
        /** @var \Magento\Tax\Model\Resource\Calculation\Rate\Collection $collection */
        $collection = Bootstrap::getObjectManager()->get('Magento\Tax\Model\Resource\Calculation\Rate\Collection');
        $dbTaxRatesQty = $collection->count();
        if (($dbTaxRatesQty == 0) || ($collection->getFirstItem()->getId() != 1)) {
            $this->fail("Preconditions failed.");
        }
        /** @var \Magento\Tax\Service\V1\Collection\TaxRateCollection $taxRatesCollection */
        $taxRatesCollection = Bootstrap::getObjectManager()
            ->create('Magento\Tax\Service\V1\Collection\TaxRateCollection');
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