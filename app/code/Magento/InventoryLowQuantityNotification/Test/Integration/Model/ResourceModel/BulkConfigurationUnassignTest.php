<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Test\Integration\Model\ResourceModel;

use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationUnassign;
use Magento\InventoryLowQuantityNotificationApi\Api\GetSourceItemConfigurationInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class BulkConfigurationUnassignTest extends TestCase
{
    /**
     * @var BulkConfigurationUnassign
     */
    private $bulkConfigurationUnassign;

    /**
     * @var GetSourceItemConfigurationInterface
     */
    private $getSourceItemConfiguration;

    public function setUp()
    {
        parent::setUp();
        $this->bulkConfigurationUnassign = Bootstrap::getObjectManager()->get(BulkConfigurationUnassign::class);
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
    public function testUnassign()
    {
        $this->bulkConfigurationUnassign->execute(['SKU-1'], ['eu-1']);
        $sourceConfig = $this->getSourceItemConfiguration->execute('eu-1', 'SKU-1');

        self::assertEquals(
            1.0, // Default value when configuration is not defined
            $sourceConfig->getNotifyStockQty(),
            'Low stock notification not removed after unassign'
        );
    }
}
