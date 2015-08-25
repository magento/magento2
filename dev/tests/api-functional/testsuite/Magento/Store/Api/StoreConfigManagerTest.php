<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Store\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for website repository interface.
 */
class StoreConfigManagerTest extends WebapiAbstract
{
    const SERVICE_NAME = 'storeStoreConfigManagerV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/store/storeConfigs';

    /**
     * Test getStoreConfigs
     */
    public function testGetStoreConfigs()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getStoreConfigs',
            ],
        ];

        $requestData = [
            'storeCodes' => ['default'],
        ];
        $storeConfigs = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotNull($storeConfigs);
        $this->assertEquals(1, count($storeConfigs));
        $expectedKeys = [
            'id',
            'code',
            'website_id',
            'locale',
            'base_currency_code',
            'default_display_currency_code',
            'timezone',
            'weight_unit',
            'base_url',
            'base_link_url',
            'base_static_url',
            'base_media_url',
            'secure_base_url',
            'secure_base_link_url',
            'secure_base_static_url',
            'secure_base_media_url'
        ];
        $this->assertEquals($expectedKeys, array_keys($storeConfigs[0]));
    }
}
