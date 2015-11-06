<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Api;

use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

class ProductLinkTypeListTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductLinkTypeListV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/';

    public function testGetItems()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'links/types',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetItems',
            ],
        ];

        $actual = $this->_webApiCall($serviceInfo);

        /**
         * Validate that product type links provided by Magento_GroupedProduct module are present
         */
        $expectedItems = ['name' => 'associated', 'code' => Link::LINK_TYPE_GROUPED];
        $this->assertContains($expectedItems, $actual);
    }

    public function testGetItemAttributes()
    {
        $linkType = 'associated';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'links/' . $linkType . '/attributes',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetItemAttributes',
            ],
        ];

        $actual = $this->_webApiCall($serviceInfo, ['type' => $linkType]);

        $expected = [
            ['code' => 'position', 'type' => 'int'],
            ['code' => 'qty', 'type' => 'decimal'],
        ];
        $this->assertEquals($expected, $actual);
    }
}
