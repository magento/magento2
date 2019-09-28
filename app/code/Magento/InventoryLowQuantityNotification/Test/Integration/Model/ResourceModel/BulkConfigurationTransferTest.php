<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Test\Integration\Model\ResourceModel;

use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationTransfer;
use Magento\InventoryLowQuantityNotificationApi\Api\GetSourceItemConfigurationInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class BulkConfigurationTransferTest extends TestCase
{
    /**
     * @var BulkConfigurationTransfer
     */
    private $bulkConfigurationTransfer;

    /**
     * @var GetSourceItemConfigurationInterface
     */
    private $getSourceItemConfiguration;

    public function setUp()
    {
        parent::setUp();
        $this->bulkConfigurationTransfer = Bootstrap::getObjectManager()->get(BulkConfigurationTransfer::class);
        $this->getSourceItemConfiguration =
            Bootstrap::getObjectManager()->create(GetSourceItemConfigurationInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/source_item_configuration.php
     * @magentoDbIsolation enabled
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testTransfer()
    {
        $sourceConfig = $this->getSourceItemConfiguration->execute('eu-1', 'SKU-1');
        $this->bulkConfigurationTransfer->execute(['SKU-1'], 'eu-1', 'eu-3');
        $destinationConfig = $this->getSourceItemConfiguration->execute('eu-3', 'SKU-1');

        self::assertEquals(
            $sourceConfig->getNotifyStockQty(),
            $destinationConfig->getNotifyStockQty(),
            'Low stock notification configuration was not transferred on bulk operations'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/source_item_configuration.php
     * @magentoDbIsolation enabled
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testTransferWithUnassign()
    {
        $sourceConfig = $this->getSourceItemConfiguration->execute('eu-1', 'SKU-1');
        $this->bulkConfigurationTransfer->execute(['SKU-1'], 'eu-1', 'eu-3');
        $destinationConfig = $this->getSourceItemConfiguration->execute('eu-3', 'SKU-1');

        self::assertEquals(
            $sourceConfig->getNotifyStockQty(),
            $destinationConfig->getNotifyStockQty(),
            'Low stock notification configuration was not transferred on bulk operations'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/source_item_configuration.php
     * @magentoDbIsolation enabled
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testTransferFromUnassignedSource()
    {
        $this->bulkConfigurationTransfer->execute(['SKU-1'], 'us-1', 'eu-1');
        $sourceConfig = $this->getSourceItemConfiguration->execute('eu-1', 'SKU-1');

        self::assertEquals(
            5.6,
            $sourceConfig->getNotifyStockQty(),
            'Low stock notification was overwritten by an unassigned source'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryLowQuantityNotificationApi/Test/_files/source_item_configuration.php
     * @magentoDbIsolation enabled
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testTransferToUnassignedSource()
    {
        $this->bulkConfigurationTransfer->execute(['SKU-1'], 'eu-1', 'us-1');
        $sourceConfig = $this->getSourceItemConfiguration->execute('us-1', 'SKU-1');

        self::assertEquals(
            5.6,
            $sourceConfig->getNotifyStockQty(),
            'Low stock notification was not transferred to unassigned source'
        );
    }
}
