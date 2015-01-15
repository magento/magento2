<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Catalog\Model\Product\Link;
use Magento\Webapi\Model\Rest\Config as RestConfig;

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
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetItems',
            ],
        ];
        $actual = $this->_webApiCall($serviceInfo);
        $expectedItems = ['name' => 'related', 'code' => Link::LINK_TYPE_RELATED];
        $this->assertContains($expectedItems, $actual);
    }

    public function testGetItemAttributes()
    {
        $linkType = 'related';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'links/' . $linkType . '/attributes',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetItemAttributes',
            ],
        ];
        $actual = $this->_webApiCall($serviceInfo, ['type' => $linkType]);
        $expected = [['code' => 'position', 'type' => 'int']];
        $this->assertEquals($expected, $actual);
    }
}
