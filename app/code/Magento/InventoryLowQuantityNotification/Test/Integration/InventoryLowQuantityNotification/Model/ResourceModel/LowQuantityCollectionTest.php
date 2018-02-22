<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreLine
namespace Magento\InventoryLowQuantityNotification\Test\Integration\InventoryLowQuantityNotification\Model\ResourceModel;

use Magento\InventoryLowQuantityNotification\Model\ResourceModel\LowQuantityCollection;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class LowQuantityCollectionTest extends TestCase
{
    /**
     * @var LowQuantityCollection
     */
    private $model;

    /**
     * Tests products from disabled sources are not showing in results.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/source_item_configuration.php
     */
    public function testInitCollection()
    {
        $expectedSourceCodes = [
            'eu-1',
            'eu-2'
        ];
        $actualSourceCodes = $this->model->getColumnValues(SourceItemConfigurationInterface::SOURCE_CODE);
        $this->assertEquals(
            $expectedSourceCodes,
            $actualSourceCodes
        );
    }

    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->create(LowQuantityCollection::class);
    }
}
