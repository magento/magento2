<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class CategoryAttributeOptionManagementInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogCategoryAttributeOptionManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/categories/attributes';

    public function testGetItems()
    {
        $this->_markTestAsRestOnly('Fix inconsistencies in WSDL and Data interfaces');
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
