<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Api;

/**
 * @magentoAppIsolation enabled
 */
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetLinkedItemsByType',
            ],
        ];

        $actual = $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'type' => $linkType]);

        $expected = [
            [
                'sku' => 'grouped-product',
                'link_type' => 'associated',
                'linked_product_sku' => 'simple-1',
                'linked_product_type' => 'simple',
                'position' => 1,
            ],
            [
                'sku' => 'grouped-product',
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
                    $item['extension_attributes'] = ['qty' => 1.0000];
                }
            );
        } else {
            array_walk(
                $expected,
                function (&$item) {
                    $item['extension_attributes'] = ['qty' => 1.0000];
                }
            );
        }
        $this->assertEquals($expected, $actual);
    }
}
