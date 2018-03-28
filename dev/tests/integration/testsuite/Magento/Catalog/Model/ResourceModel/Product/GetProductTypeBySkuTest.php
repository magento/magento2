<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests getting Product type by product SKU.
 */
class GetProductTypeBySkuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetProductTypeBySku
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->create(
            GetProductTypeBySku::class
        );
    }

    /**
     * Tests getting correct product type by product sku.
     *
     * @param array $skus
     * @param array $expectedTypes
     * @magentoDataFixture Magento/Catalog/_files/products_all_types.php
     * @dataProvider productTypesDataProvider
     */
    public function testProductTypes(array $skus, array $expectedTypes)
    {
        $actualTypes = $this->model->execute($skus);
        $this->assertEquals(
            $expectedTypes,
            $actualTypes
        );
    }

    /**
     * Data provider for testProductTypes.
     *
     * @return array
     */
    public function productTypesDataProvider()
    {
        return [
            [
                [
                    'bundle_sku',
                    'configurable_sku',
                    'simple_sku',
                    'downloadable_sku',
                    'grouped_sku',
                    'virtual_sku',
                ],
                [
                    'bundle_sku' => 'bundle',
                    'configurable_sku' => 'configurable',
                    'simple_sku' => 'simple',
                    'downloadable_sku' => 'downloadable',
                    'grouped_sku' => 'grouped',
                    'virtual_sku' => 'virtual',
                ],
            ],
            [['non_exists_sku'], []],
            [[], []],
        ];
    }
}