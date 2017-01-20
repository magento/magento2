<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductRepositoryMultiCurrencyTest extends WebapiAbstract
{
    const PRODUCT_SERVICE_NAME = 'catalogProductRepositoryV1';
    const WEBSITES_SERVICE_NAME = 'storeWebsiteRepositoryV1';
    const WEBSITE_LINK_SERVICE_NAME = 'catalogProductWebsiteLinkRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const PRODUCTS_RESOURCE_PATH = '/V1/products';
    const WEBSITES_RESOURCE_PATH = '/V1/store/websites';

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_second_currency.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGet()
    {
        $sku = 'simple';
        $this->assignProductToWebsite($sku, $this->getWebsiteId('test'));
        $product = $this->getProduct($sku);
        $this->assertEquals(10, $product['price']);

        $product['price'] = 20;
        $this->saveProduct($product, 'fixture_second_store');

        $product = $this->getProduct($sku, 'fixture_third_store');
        $this->assertEquals(20, $product['price']);
    }

    private function saveProduct($product, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $product['sku'],
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::PRODUCT_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::PRODUCT_SERVICE_NAME . 'Save'
            ]
        ];

        $requestData = ['product' => $product];
        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    private function getProduct($sku, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::PRODUCTS_RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::PRODUCT_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::PRODUCT_SERVICE_NAME . 'get'
            ]
        ];

        $requestData = ['sku' => $sku];
        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    private function getWebsiteId($websiteCode)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::WEBSITES_RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::WEBSITES_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::WEBSITES_SERVICE_NAME . 'GetList'
            ]
        ];

        $response = $this->_webApiCall($serviceInfo);
        $websiteId = null;
        foreach ($response as $website) {
            if ($website['code'] == $websiteCode) {
                $websiteId = $website['id'];
            }
        }
        $this->assertNotNull($websiteId);
        return $websiteId;
    }

    private function assignProductToWebsite($sku, $websiteId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $sku . '/websites',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::WEBSITE_LINK_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::WEBSITE_LINK_SERVICE_NAME . 'save'
            ]
        ];

        $requestData = [
            "productWebsiteLink" => [
                "websiteId" => $websiteId,
                "sku" => $sku
            ]
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
    }
}
