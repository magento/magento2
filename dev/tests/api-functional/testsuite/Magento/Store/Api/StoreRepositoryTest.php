<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Store\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for store repository interface.
 */
class StoreRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'storeStoreRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/store/storeViews';

    /**
     * Test getList
     */
    public function testGetList()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $requestData = [];
        $storeViews = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($storeViews);
        $this->assertGreaterThan(1, count($storeViews));
        $keys = ['id', 'code', 'name', 'website_id', 'store_group_id', 'is_active'];
        $this->assertEquals($keys, array_keys($storeViews[0]));
    }
}
