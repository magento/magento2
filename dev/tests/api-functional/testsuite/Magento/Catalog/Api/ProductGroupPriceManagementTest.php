<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class ProductGroupPriceManagementTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductGroupPriceManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/';

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_group_prices.php
     */
    public function testGetList()
    {
        $productSku = 'simple_with_group_price';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . '/group-prices',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        $groupPriceList = $this->_webApiCall($serviceInfo, ['productSku' => $productSku]);
        $this->assertCount(2, $groupPriceList);
        $this->assertEquals(9, $groupPriceList[0]['value']);
        $this->assertEquals(7, $groupPriceList[1]['value']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_group_prices.php
     */
    public function testDelete()
    {
        $productSku = 'simple_with_group_price';
        $customerGroupId = \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $productSku . "/group-prices/" . $customerGroupId,
                'httpMethod' => RestConfig::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Remove',
            ],
        ];
        $requestData = ['productSku' => $productSku, 'customerGroupId' => $customerGroupId];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testAdd()
    {
        $productSku = 'simple';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku . '/group-prices/1/price/10',
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Add',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['productSku' => $productSku, 'customerGroupId' => 1, 'price' => 10]);
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Api\ProductGroupPriceManagementInterface $service */
        $service = $objectManager->get('Magento\Catalog\Api\ProductGroupPriceManagementInterface');
        $prices = $service->getList($productSku);
        $this->assertCount(1, $prices);
        $this->assertEquals(10, $prices[0]->getValue());
        $this->assertEquals(1, $prices[0]->getCustomerGroupId());
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/Store/_files/website.php
     */
    public function testAddForDifferentWebsite()
    {
        $productSku = 'simple';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku . '/group-prices/1/price/10',
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Add',
            ],

        ];
        $this->_webApiCall($serviceInfo, ['productSku' => $productSku, 'customerGroupId' => 1, 'price' => 10]);
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        /** @var \Magento\Catalog\Api\ProductGroupPriceManagementInterface $service */
        $service = $objectManager->get('Magento\Catalog\Api\ProductGroupPriceManagementInterface');
        $prices = $service->getList($productSku);
        $this->assertCount(1, $prices);
        $this->assertEquals(10, $prices[0]->getValue());
        $this->assertEquals(1, $prices[0]->getCustomerGroupId());
    }
}
