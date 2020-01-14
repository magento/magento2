<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for (store) group repository interface.
 */
class GroupRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'storeGroupRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/store/storeGroups';

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
        $storeGroups = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($storeGroups);
        $this->assertGreaterThan(1, count($storeGroups));
        $keys = ['id', 'website_id', 'root_category_id', 'default_store_id', 'name', 'code'];
        $this->assertEquals($keys, array_keys($storeGroups[0]));
    }
}
