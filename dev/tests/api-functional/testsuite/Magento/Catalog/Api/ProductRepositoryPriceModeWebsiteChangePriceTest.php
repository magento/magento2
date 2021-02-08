<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductRepositoryPriceModeWebsiteChangePriceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const PRODUCTS_RESOURCE_PATH = '/V1/products';
    const PRICES_RESOURCE_PATH = '/V1/products/base-prices';
    const STORE1_CODE_FROM_FIXTURE = 'fixturestore';
    const STORE2_CODE_FROM_FIXTURE = 'fixture_second_store';

    /**
     * @magentoApiDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @magentoConfigFixture base_website catalog/price/scope 1
     */
    public function testChangePriceForStore()
    {
        /** @var $store1 \Magento\Store\Model\Group   */
        $store1 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
        $store1->load(self::STORE1_CODE_FROM_FIXTURE);

        /** @var $store2 \Magento\Store\Model\Group   */
        $store2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
        $store2->load(self::STORE2_CODE_FROM_FIXTURE);

        $sku = 'simple';

        $requestData1 = ['prices' => [
            'price' => 20,
            'store_id' => $store1->getId(),
            'sku' => $sku
        ]];

        $requestData2 = ['prices' => [
            'price' => 30,
            'store_id' => $store2->getId(),
            'sku' => $sku
        ]];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::PRODUCTS_RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData1);
        $this->_webApiCall($serviceInfo, $requestData2);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $product->setStoreId($store1->getId())->load($product->getIdBySku($sku));

        $this->assertEquals(
            $product->getPrice(),
            30,
            'Product prices for Website Price mode is invalid.'
        );
    }
}
