<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Test\Api;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalog\Api\DefaultSourceRepositoryInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class GetDefaultSourceTest
 */
class GetDefaultSourceTest extends WebapiAbstract
{
    /**
     * Test that default Source is present after installation
     */
    public function testGetDefaultSource()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/source/' . DefaultSourceRepositoryInterface::DEFAULT_SOURCE,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiSourceRepositoryV1',
                'operation' => 'inventoryApiSourceRepositoryV1Get',
            ],
        ];
        if (self::ADAPTER_REST == TESTS_WEB_API_ADAPTER) {
            $source = $this->_webApiCall($serviceInfo);
        } else {
            $source = $this->_webApiCall($serviceInfo, ['sourceId' => DefaultSourceRepositoryInterface::DEFAULT_SOURCE]);
        }
        $this->assertEquals(DefaultSourceRepositoryInterface::DEFAULT_SOURCE, $source[SourceInterface::SOURCE_ID]);
    }
}
