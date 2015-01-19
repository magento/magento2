<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class ProductRepositoryMultiStoreTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';
    const STORE_CODE_FROM_FIXTURE = 'fixturestore';

    private $productData = [
        [
            Product::SKU => 'simple',
            Product::NAME => 'Simple Related Product',
            Product::TYPE_ID => 'simple',
            Product::PRICE => 10
        ],
        [
            Product::SKU => 'simple_with_cross',
            Product::NAME => 'Simple Product With Related Product',
            Product::TYPE_ID => 'simple',
            Product::PRICE => 10
        ],
    ];

    /**
     * Create another store one time for testSearch
     * @magentoApiDataFixture Magento/Core/_files/store.php
     */
    public function testCreateAnotherStore()
    {
        /** @var $store \Magento\Store\Model\Store */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
        $store->load('fixturestore');
        $this->assertNotNull($store->getId());
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @depends testCreateAnotherStore
     */
    public function testGetMultiStore()
    {
        $productData = $this->productData[0];
        $nameInFixtureStore = 'Name in fixture store';
        /** @var $store \Magento\Store\Model\Group   */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
        $store->load(self::STORE_CODE_FROM_FIXTURE);
        $this->assertNotNull($store->getId(), 'Precondition failed: fixture store was not created.');
        $sku = $productData[Product::SKU];
        /** @var \Magento\Catalog\Model\Product $product */
        $product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $product->load($product->getIdBySku($sku));
        $product->setName($nameInFixtureStore)->setStoreId($store->getId())->save();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => RestConfig::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'get'
            ]
        ];

        $requestData = ['id' => $sku, 'productSku' => $sku];
        $defaultStoreResponse = $this->_webApiCall($serviceInfo, $requestData);
        $nameInDefaultStore = 'Simple Product';
        $this->assertEquals(
            $nameInDefaultStore,
            $defaultStoreResponse[Product::NAME],
            'Product name in default store is invalid.'
        );
        $fixtureStoreResponse = $this->_webApiCall($serviceInfo, $requestData, null, self::STORE_CODE_FROM_FIXTURE);
        $this->assertEquals(
            $nameInFixtureStore,
            $fixtureStoreResponse[Product::NAME],
            'Product name in fixture store is invalid.'
        );
    }

    /**
     * Remove test store
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        /** @var \Magento\Framework\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var $store \Magento\Store\Model\Store */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
        $store->load('fixturestore');
        if ($store->getId()) {
            $store->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
