<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Test\Api;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class GetDefaultSource
 */
class GetDefaultSource extends WebapiAbstract
{
    /**
     * Get default source from WebApi test
     */
    public function testGetDefaultSource()
    {
        $defaultSourceId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/source/' . $defaultSourceId,
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
            $source = $this->_webApiCall($serviceInfo, ['sourceId' => $defaultSourceId]);
        }
        $this->assertEquals($defaultSourceId, $source[SourceInterface::SOURCE_ID]);
    }
}
