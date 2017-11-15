<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfiguration\Test\Integration\Model\ResourceModel\SourceItemConfiguration;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryConfiguration\Model\SourceItemConfiguration\DeleteInterface as InventoryItemConfigurationDelete;
use Magento\InventoryConfigurationApi\Api\GetSourceItemConfigurationInterface;

class DeleteSourceItemConfigurationTest extends TestCase
{
    /**
     * @var GetSourceItemConfigurationInterface
     */
    protected $getSourceItemConfiguration;

    /**
     * @var SourceItemConfigurationInterfaceFactory
     */
    private $sourceItemConfigurationFactory;

    /**
     * @var InventoryItemConfigurationDelete
     */
    private $inventoryItemConfigurationDelete;

    protected function setUp()
    {
        $this->sourceItemConfigurationFactory = Bootstrap::getObjectManager()
            ->create(SourceItemConfigurationInterfaceFactory::class);
        $this->inventoryItemConfigurationDelete = Bootstrap::getObjectManager()
            ->create(InventoryItemConfigurationDelete::class);
        $this->getSourceItemConfiguration = Bootstrap::getObjectManager()
            ->create(GetSourceItemConfigurationInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurationApi/Test/_files/source_item_configuration.php
     */
    public function testDeleteSourceItemConfiguration()
    {
        $sourceItemId = 1;
        $sourceId = 10;
        $notifyStockQty = 2;
        $sku = 'SKU-1';

        /** @var SourceItemConfigurationInterface  $sourceItemConfiguration */
        $sourceItemConfiguration = $this->sourceItemConfigurationFactory->create();
        $sourceItemConfiguration->setSourceItemId($sourceItemId);
        $sourceItemConfiguration->setNotifyStockQty($notifyStockQty);

        $itemConfigurationFromDb = $this->getItemConfiguration($sourceId, $sku);
        $this->assertEquals(1, $itemConfigurationFromDb->getSourceItemId());

        $this->inventoryItemConfigurationDelete->delete($sourceItemConfiguration);

        $itemConfigurationFromDb = $this->getItemConfiguration($sourceId, $sku);

        $this->assertEquals(null, $itemConfigurationFromDb->getSourceItemId());
    }

    /**
     * @param $sourceItemId
     * @param $sku
     * @return SourceItemConfigurationInterface
     */
    private function getItemConfiguration($sourceItemId, $sku)
    {
        return $this->getSourceItemConfiguration->get($sourceItemId, $sku);
    }
}