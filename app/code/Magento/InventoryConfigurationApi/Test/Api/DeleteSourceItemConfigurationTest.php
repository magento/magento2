<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Test\Api;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryConfigurationApi\Api\GetSourceItemConfigurationInterface;
use Magento\Framework\Webapi\Rest\Request;

class DeleteSourceItemConfigurationTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/configuration';
    const SERVICE_NAME = 'inventoryConfigurationApiDeleteSourceItemConfigurationV1';

    /**
     * @var GetSourceItemConfigurationInterface
     */
    protected $getSourceItemConfiguration;

    /**
     * @var SourceItemConfigurationInterfaceFactory
     */
    private $sourceItemConfigurationFactory;


    protected function setUp()
    {
        $this->sourceItemConfigurationFactory = Bootstrap::getObjectManager()
            ->create(SourceItemConfigurationInterfaceFactory::class);
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
        $sourceId = 10;
        $notifyStockQty = 2;
        $sku = 'SKU-1';

        /** @var SourceItemConfigurationInterface  $sourceItemConfiguration */
        $sourceItemConfiguration = $this->sourceItemConfigurationFactory->create();
        $sourceItemConfiguration->setSourceId($sourceId);
        $sourceItemConfiguration->setNotifyStockQty($notifyStockQty);

        $itemConfigurationFromDb = $this->getItemConfiguration($sourceId, $sku);
        $this->assertEquals(2, $itemConfigurationFromDb->getNotifyStockQty());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?'
                    . http_build_query(['sourceId' => $sourceId, 'sku' => $sku]),
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $response = $this->_webApiCall($serviceInfo);
        } else {
            $response =$this->_webApiCall($serviceInfo, ['sourceId' => $sourceId, 'sku' => $sku]);
        }

        $itemConfigurationFromDb = $this->getItemConfiguration($sourceId, $sku);

        $this->assertEquals(null, $itemConfigurationFromDb->getNotifyStockQty());
    }

    /**
     * @param $sourceId
     * @param $sku
     * @return SourceItemConfigurationInterface
     */
    private function getItemConfiguration(int $sourceId, string $sku): SourceItemConfigurationInterface
    {
        return $this->getSourceItemConfiguration->get($sourceId, $sku);
    }
}