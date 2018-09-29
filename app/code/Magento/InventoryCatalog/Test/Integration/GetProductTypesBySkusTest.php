<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests getting Product type by product SKU.
 */
class GetProductTypesBySkusTest extends TestCase
{
    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->getProductTypesBySkus = Bootstrap::getObjectManager()->get(GetProductTypesBySkusInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/products_all_types.php
     */
    public function testExecute()
    {
        $typesBySku = [
            'bundle_sku' => 'bundle',
            'configurable_sku' => 'configurable',
            'simple_sku' => 'simple',
            'downloadable_sku' => 'downloadable',
            'grouped_sku' => 'grouped',
            'virtual_sku' => 'virtual',
        ];

        self::assertEquals($typesBySku, $this->getProductTypesBySkus->execute(array_keys($typesBySku)));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/products_all_types.php
     */
    public function testExecuteWithNotExistedSkus()
    {
        $skus = ['not_existed_1', 'not_existed_2', 'simple_sku'];

        self::assertEquals(['simple_sku' => 'simple'], $this->getProductTypesBySkus->execute($skus));
    }
}
