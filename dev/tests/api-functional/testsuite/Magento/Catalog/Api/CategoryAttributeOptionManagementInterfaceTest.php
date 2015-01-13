<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class CategoryAttributeOptionManagementInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogCategoryAttributeOptionManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/categories/attributes';

    public function testGetItems()
    {
        $testAttributeCode = 'include_in_menu';
        $expectedOptions = [
            [
                    'label' => 'Yes',
                    'value' => '1',
            ],
            [
                    'label' => 'No',
                    'value' => '0',
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $testAttributeCode . '/options',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getItems',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['attributeCode' => $testAttributeCode]);

        $this->assertTrue(is_array($response));
        $this->assertEquals($expectedOptions, $response);
    }
}
