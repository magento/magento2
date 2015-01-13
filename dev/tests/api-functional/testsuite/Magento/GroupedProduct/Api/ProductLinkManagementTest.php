<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Api;

use Magento\Webapi\Model\Rest\Config as RestConfig;

class ProductLinkManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductLinkManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/';

    /**
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testGetLinkedItemsByType()
    {
        $productSku = 'grouped-product';
        $linkType = 'associated';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . '/links/' . $linkType,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetLinkedItemsByType',
            ],
        ];

        $actual = $this->_webApiCall($serviceInfo, ['productSku' => $productSku, 'type' => $linkType]);

        $expected = [
            [
                'product_sku' => 'grouped-product',
                'link_type' => 'associated',
                'linked_product_sku' => 'simple-1',
                'linked_product_type' => 'simple',
                'position' => 1,
            ],
            [
                'product_sku' => 'grouped-product',
                'link_type' => 'associated',
                'linked_product_sku' => 'virtual-product',
                'linked_product_type' => 'virtual',
                'position' => 2,
            ],
        ];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            array_walk(
                $expected,
                function (&$item) {
                    $item['custom_attributes'] = [['attribute_code' => 'qty', 'value' => 1.0000]];
                }
            );
        } else {
            array_walk(
                $expected,
                function (&$item) {
                    $item['custom_attributes'] = [['attribute_code' => 'qty', 'value' => 1.0000]];
                }
            );
        }
        $this->assertEquals($expected, $actual);
    }
}
