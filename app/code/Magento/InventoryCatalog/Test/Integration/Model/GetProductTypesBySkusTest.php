<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\Model;

use Magento\InventoryCatalog\Model\GetProductTypesBySkus;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests getting Product type by product SKU.
 */
class GetProductTypesBySkusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GetProductTypesBySkus
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->create(
            GetProductTypesBySkus::class
        );
    }

    /**
     * Tests getting correct product type by product sku.
     *
     * @param array $skus
     * @param array $expectedTypes
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/products_all_types.php
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
