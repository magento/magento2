<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractEavTest;

/**
 * Provides tests for product form data provider eav modifier for default attributes (sku, price, status, name).
 *
 * @magentoDbIsolation enabled
 */
class DefaultAttributesTest extends AbstractEavTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testModifyMeta(): void
    {
        $expectedMeta = include __DIR__ . '/../_files/eav_expected_meta_output.php';
        $this->callModifyMetaAndAssert($this->getProduct(), $expectedMeta);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_admin_store.php
     * @return void
     */
    public function testModifyData(): void
    {
        $expectedData = include __DIR__ . '/../_files/eav_expected_data_output.php';
        // force load: ProductRepositoryInterface::getList does not add stock item, prices, categories to product
        $this->callModifyDataAndAssert($this->getProduct(true), $expectedData);
    }

    /**
     * @return void
     */
    public function testModifyMetaNewProduct(): void
    {
        $expectedMeta = include __DIR__ . '/../_files/eav_expected_meta_output_w_default.php';
        $this->callModifyMetaAndAssert($this->getNewProduct(), $expectedMeta);
    }
}
