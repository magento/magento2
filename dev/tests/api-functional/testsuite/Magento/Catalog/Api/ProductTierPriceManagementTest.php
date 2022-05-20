<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * ProductTierPriceManagementTest API operations test
 */
class ProductTierPriceManagementTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'catalogProductTierPriceManagementV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products/';

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getListDataProvider
     */
    public function testGetList($customerGroupId, $count, $value, $qty)
    {
        $productSku = 'simple';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . '/group-prices/' . $customerGroupId . '/tiers',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $tierPriceList = $this->_webApiCall(
            $serviceInfo,
            ['sku' => $productSku, 'customerGroupId' => $customerGroupId]
        );

        $this->assertCount($count, $tierPriceList);
        if ($count) {
            $this->assertEquals($value, $tierPriceList[0]['value']);
            $this->assertEquals($qty, $tierPriceList[0]['qty']);
        }
    }

    public function getListDataProvider()
    {
        return [
            [0, 3, 5, 3],
            [1, 0, null, null],
            ['all', 2, 8, 2],
        ];
    }

    /**
     * @param string|int $customerGroupId
     * @param int $qty
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider deleteDataProvider
     */
    public function testDelete($customerGroupId, $qty)
    {
        $productSku = 'simple';
        $serviceInfo = [
            'rest' => [
                'resourcePath' =>   self::RESOURCE_PATH
                    . $productSku . "/group-prices/" . $customerGroupId . "/tiers/" . $qty,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Remove',
            ],
        ];
        $requestData = ['sku' => $productSku, 'customerGroupId' => $customerGroupId, 'qty' => $qty];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData, null, "all"));
    }

    public function deleteDataProvider()
    {
        return [
            'delete_tier_price_for_specific_customer_group' => [0, 3],
            'delete_tier_price_for_all_customer_group' => ['all', 5]
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testAdd()
    {
        $productSku = 'simple';
        $customerGroupId = 1;
        $qty = 50;
        $price = 10;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku
                    . '/group-prices/' . $customerGroupId . '/tiers/' . $qty . '/price/' . $price,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Add',
            ],
        ];

        $requestData = [
            'sku' => $productSku,
            'customerGroupId' => $customerGroupId,
            'qty' => $qty,
            'price' => $price,
        ];
        $this->_webApiCall($serviceInfo, $requestData);
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Api\ProductTierPriceManagementInterface $service */
        $service = $objectManager->get(\Magento\Catalog\Api\ProductTierPriceManagementInterface::class);
        $prices = $service->getList($productSku, 1);
        $this->assertCount(1, $prices);
        $this->assertEquals(10, $prices[0]->getValue());
        $this->assertEquals(50, $prices[0]->getQty());
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testAddWithAllCustomerGrouped()
    {
        $productSku = 'simple';
        $customerGroupId = 'all';
        $qty = 50;
        $price = 20;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku
                    . '/group-prices/' . $customerGroupId . '/tiers/' . $qty . '/price/' . $price,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Add',
            ],
        ];
        $requestData = [
            'sku' => $productSku,
            'customerGroupId' => $customerGroupId,
            'qty' => $qty,
            'price' => $price,
        ];
        $this->_webApiCall($serviceInfo, $requestData);
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Api\ProductTierPriceManagementInterface $service */
        $service = $objectManager->get(\Magento\Catalog\Api\ProductTierPriceManagementInterface::class);
        $prices = $service->getList($productSku, 'all');
        $this->assertCount(3, $prices);
        $this->assertEquals(20, (int)$prices[2]->getValue());
        $this->assertEquals(50, (int)$prices[2]->getQty());
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testUpdateWithAllGroups()
    {
        $productSku = 'simple';
        $customerGroupId = 'all';
        $qty = 2;
        $price = 20;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku
                    . '/group-prices/' . $customerGroupId . '/tiers/' . $qty . '/price/' . $price,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Add',
            ],
        ];
        $requestData = [
            'sku' => $productSku,
            'customerGroupId' => $customerGroupId,
            'qty' => $qty,
            'price' => $price,
        ];
        $this->_webApiCall($serviceInfo, $requestData, null, "all");
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Api\ProductTierPriceManagementInterface $service */
        $service = $objectManager->get(\Magento\Catalog\Api\ProductTierPriceManagementInterface::class);
        $prices = $service->getList($productSku, 'all');
        $this->assertCount(2, $prices);
        $this->assertEquals(20, (int)$prices[0]->getValue());
        $this->assertEquals(2, (int)$prices[0]->getQty());
    }
}
