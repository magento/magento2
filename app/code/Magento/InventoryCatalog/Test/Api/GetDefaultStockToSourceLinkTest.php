<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Test\Api;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalog\Api\DefaultSourceRepositoryInterface;
use Magento\InventoryCatalog\Api\DefaultStockRepositoryInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class GetDefaultStockToSourceLinkTest
 */
class GetDefaultStockToSourceLinkTest extends WebapiAbstract
{
    /**
     * Test that default Stock is present after installation
     */
    public function testGetDefaultStockToSourceLink()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/stock/get-assigned-sources/' .
                    DefaultStockRepositoryInterface::DEFAULT_STOCK,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiGetAssignedSourcesForStockV1',
                'operation' => 'inventoryApiStockRepositoryV1Get',
            ],
        ];
        if (self::ADAPTER_REST == TESTS_WEB_API_ADAPTER) {
            $source = $this->_webApiCall($serviceInfo);
        } else {
            $source = $this->_webApiCall($serviceInfo, ['stockId' => DefaultStockRepositoryInterface::DEFAULT_STOCK]);
        }
        $this->assertEquals(
            [DefaultSourceRepositoryInterface::DEFAULT_SOURCE],
            array_column($source, SourceInterface::SOURCE_ID)
        );
    }
}
