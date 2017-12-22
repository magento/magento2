<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api;

use Magento\InventoryApi\Api\Data\SourceInterface;
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
        $defaultStockId = 1;
        $defaultSourceCode = 'default';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/stock/get-assigned-sources/' . $defaultStockId,
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
            $source = $this->_webApiCall($serviceInfo, ['stockId' => $defaultStockId]);
        }
        $this->assertEquals([$defaultSourceCode], array_column($source, SourceInterface::CODE));
    }
}
