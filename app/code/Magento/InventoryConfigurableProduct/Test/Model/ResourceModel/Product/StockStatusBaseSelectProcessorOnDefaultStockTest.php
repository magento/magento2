<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\CatalogInventory\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class StockStatusBaseSelectProcessorOnDefaultStockTest extends TestCase
{
    /**
     * @var BaseSelectProcessorInterface
     */
    private $productResourceModel;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productResourceModel = Bootstrap::getObjectManager()->get(BaseSelectProcessorInterface::class);
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable_out_of_stock.php
     *
     * @return void
     */
    public function testProcess()
    {
        $select = $this->resourceConnection->getConnection()->select();
        $productTable = $this->resourceConnection->getTableName('catalog_product_entity');
        $select
            ->from(['parent' => $productTable])
            ->joinInner(
                ['link' => $this->resourceConnection->getTableName('catalog_product_relation')],
                'link.parent_id = parent.entity_id',
                []
            )
            ->joinInner(
                ['child' => $productTable],
                'child.entity_id = link.child_id',
                []
            );

        $this->productResourceModel->process($select);

        self::assertEquals(2, count($select->query()->fetchAll()));
    }
}
