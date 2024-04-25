<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Store\Model\Store;

class ProductRepositoryPriceModeWebsiteChangePriceTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'catalogProductRepositoryV1';
    private const PRICE_SERVICE_NAME = 'catalogBasePriceStorageV1';
    private const SERVICE_VERSION = 'V1';
    private const PRODUCTS_RESOURCE_PATH = '/V1/products';
    private const PRICES_RESOURCE_PATH = '/V1/products/base-prices';
    private const STORE1_CODE_FROM_FIXTURE = 'fixturestore';
    private const STORE2_CODE_FROM_FIXTURE = 'fixture_second_store';

    /**
     * @magentoApiDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture admin_store catalog/price/scope 1
     */
    public function testChangePriceForStore()
    {
        /** @var $store1 \Magento\Store\Model\Group   */
        $store1 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Store::class);
        $store1->load(self::STORE1_CODE_FROM_FIXTURE);

        /** @var $store2 \Magento\Store\Model\Group   */
        $store2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Store::class);
        $store2->load(self::STORE2_CODE_FROM_FIXTURE);

        $sku = 'simple';

        $requestData1 = [
            'prices' => [
                [
                    'price' => 20,
                    'store_id' => $store1->getId(),
                    'sku' => $sku
                ]
            ]
        ];

        $requestData2 = [
            'prices' => [
                [
                    'price' => 30,
                    'store_id' => $store2->getId(),
                    'sku' => $sku
                ]
            ]
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::PRICES_RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::PRICE_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::PRICE_SERVICE_NAME . 'Update',
            ]
        ];

        $this->_webApiCall($serviceInfo, $requestData1);
        $this->_webApiCall($serviceInfo, $requestData2);

        /** @var \Magento\Catalog\Model\ProductRepository $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\ProductRepository::class);
        $product = $productRepository->get($sku, false, $store1->getId());

        $this->assertEquals(
            $product->getPrice(),
            30,
            'Product prices for Website Price mode is invalid.'
        );
    }
}
