<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Weee;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for storeConfig FPT config values
 *
 * @magentoDbIsolation enabled
 */
class StoreConfigFPTTest extends GraphQlAbstract
{
    /**
     * @magentoConfigFixture default/tax/weee/enable 1
     * @magentoConfigFixture default/tax/weee/display 0
     * @magentoConfigFixture default/tax/weee/display_list 0
     * @magentoConfigFixture default/tax/weee/display_sales 0
     */
    public function testWeeTaxSettingsDisplayIncludedOnly()
    {
        $query = $this->getStoreConfigQuery();
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);

        $this->assertNotEmpty($result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['sales_fixed_product_tax_display_setting']);

        $displayValue = 'INCLUDE_FPT_WITHOUT_DETAILS';
        $this->assertEquals($displayValue, $result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['sales_fixed_product_tax_display_setting']);
    }

    /**
     * @magentoConfigFixture default/tax/weee/enable 1
     * @magentoConfigFixture default/tax/weee/display 1
     * @magentoConfigFixture default/tax/weee/display_list 1
     * @magentoConfigFixture default/tax/weee/display_sales 1
     */
    public function testWeeTaxSettingsDisplayIncludedAndDescription()
    {
        $query = $this->getStoreConfigQuery();
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);

        $this->assertNotEmpty($result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['sales_fixed_product_tax_display_setting']);

        $displayValue = 'INCLUDE_FPT_WITH_DETAILS';
        $this->assertEquals($displayValue, $result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['sales_fixed_product_tax_display_setting']);
    }

    /**
     * @magentoConfigFixture default/tax/weee/enable 1
     * @magentoConfigFixture default/tax/weee/display 2
     * @magentoConfigFixture default/tax/weee/display_list 2
     * @magentoConfigFixture default/tax/weee/display_sales 2
     */
    public function testWeeTaxSettingsDisplayIncludedAndExcludedAndDescription()
    {
        $query = $this->getStoreConfigQuery();
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);

        $this->assertNotEmpty($result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['sales_fixed_product_tax_display_setting']);

        $displayValue = 'EXCLUDE_FPT_AND_INCLUDE_WITH_DETAILS';
        $this->assertEquals($displayValue, $result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['sales_fixed_product_tax_display_setting']);
    }

    /**
     * @magentoConfigFixture default/tax/weee/enable 1
     * @magentoConfigFixture default/tax/weee/display 3
     * @magentoConfigFixture default/tax/weee/display_list 3
     * @magentoConfigFixture default/tax/weee/display_sales 3
     */
    public function testWeeTaxSettingsDisplayExcluded()
    {
        $query = $this->getStoreConfigQuery();
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);

        $this->assertNotEmpty($result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['sales_fixed_product_tax_display_setting']);

        $displayValue = 'EXCLUDE_FPT_WITHOUT_DETAILS';
        $this->assertEquals($displayValue, $result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['sales_fixed_product_tax_display_setting']);
    }

    /**
     * @magentoConfigFixture default/tax/weee/enable 0
     * @magentoConfigFixture default/tax/weee/display 3
     * @magentoConfigFixture default/tax/weee/display_list 3
     * @magentoConfigFixture default/tax/weee/display_sales 3
     */
    public function testWeeTaxSettingsDisabled()
    {
        $query = $this->getStoreConfigQuery();
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);

        $this->assertNotEmpty($result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['sales_fixed_product_tax_display_setting']);

        $displayValue = 'FPT_DISABLED';
        $this->assertEquals($displayValue, $result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertEquals($displayValue, $result['storeConfig']['sales_fixed_product_tax_display_setting']);
    }

    /**
     * FPT Display setting shuffled
     *
     * @magentoConfigFixture default/tax/weee/enable 1
     * @magentoConfigFixture default/tax/weee/display 0
     * @magentoConfigFixture default/tax/weee/display_list 1
     * @magentoConfigFixture default/tax/weee/display_sales 2
     */
    public function testDifferentFPTDisplaySettings()
    {
        $query = $this->getStoreConfigQuery();
        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);

        $this->assertNotEmpty($result['storeConfig']['product_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['category_fixed_product_tax_display_setting']);
        $this->assertNotEmpty($result['storeConfig']['sales_fixed_product_tax_display_setting']);

        $this->assertEquals(
            'INCLUDE_FPT_WITHOUT_DETAILS',
            $result['storeConfig']['product_fixed_product_tax_display_setting']
        );
        $this->assertEquals(
            'INCLUDE_FPT_WITH_DETAILS',
            $result['storeConfig']['category_fixed_product_tax_display_setting']
        );
        $this->assertEquals(
            'EXCLUDE_FPT_AND_INCLUDE_WITH_DETAILS',
            $result['storeConfig']['sales_fixed_product_tax_display_setting']
        );
    }

    /**
     * Get GraphQl query to fetch storeConfig and FPT serttings
     *
     * @return string
     */
    private function getStoreConfigQuery(): string
    {
        return <<<QUERY
{
    storeConfig {
          product_fixed_product_tax_display_setting
          category_fixed_product_tax_display_setting
          sales_fixed_product_tax_display_setting
    }
}
QUERY;
    }
}
