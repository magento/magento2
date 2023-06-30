<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Product;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\ObjectManagerInterface;

/**
 * Test for \Magento\Catalog\Api\ProductRepositoryInterface
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ProductRepositoryMultiWebsiteTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    /**
     * @var array
     */
    private $fixtureProducts = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->deleteFixtureProducts();
    }

    /**
     * Save Product
     *
     * @param array $product
     * @param string|null $storeCode
     * @param string|null $token
     * @return mixed
     */
    private function saveProduct(array $product, ?string $storeCode = null, ?string $token = null)
    {
        if (isset($product['custom_attributes'])) {
            foreach ($product['custom_attributes'] as &$attribute) {
                if ($attribute['attribute_code'] == 'category_ids'
                    && !is_array($attribute['value'])
                ) {
                    $attribute['value'] = [""];
                }
            }
        }
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        if ($token) {
            $serviceInfo['rest']['token'] = $serviceInfo['soap']['token'] = $token;
        }
        $requestData = ['product' => $product];

        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    /**
     * Delete Product
     *
     * @param string $sku
     * @return boolean
     */
    private function deleteProduct(string $sku) : bool
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        return (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['sku' => $sku]) : $this->_webApiCall($serviceInfo);
    }

    /**
     * @return void
     */
    private function deleteFixtureProducts(): void
    {
        foreach ($this->fixtureProducts as $sku) {
            $this->deleteProduct($sku);
        }
        $this->fixtureProducts = [];
    }

    /**
     * Test that updating some values for product for specified store won't uncheck 'use default values'
     * for attributes which weren't changed
     *
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     */
    public function testProductDefaultValuesWithTwoWebsites(): void
    {
        $productData = [
            ProductInterface::SKU => 'test-1',
            ProductInterface::NAME => 'Test 1',
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            ProductInterface::PRICE => 10,
            ProductInterface::STATUS => 1,
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::WEIGHT => 100,
        ];

        $response = $this->saveProduct($productData, 'all');

        $this->assertEquals($response[ProductInterface::SKU], $productData[ProductInterface::SKU]);

        $this->fixtureProducts[] = $productData[ProductInterface::SKU];

        $productEditData = [
            ProductInterface::SKU => 'test-1',
            ProductInterface::NAME => 'Test 1 changed',
        ];

        $responseAfterEdit = $this->saveProduct($productEditData, 'fixture_third_store');

        $this->assertEquals($productEditData[ProductInterface::NAME], $responseAfterEdit[ProductInterface::NAME]);

        $store = $this->objectManager->get(Store::class);
        /** @var Product $model */
        $model = $this->objectManager->get(Product::class);
        $product = $model->load($model->getIdBySku($responseAfterEdit[ProductInterface::SKU]));

        /** @var ScopeOverriddenValue $scopeOverriddenValue */
        $scopeOverriddenValue = $this->objectManager->get(ScopeOverriddenValue::class);
        $storeId = $store->load('fixture_third_store', 'code')->getId();
        $this->assertFalse($scopeOverriddenValue->containsValue(
            ProductInterface::class,
            $product,
            'visibility',
            $storeId
        ));

        $this->assertFalse($scopeOverriddenValue->containsValue(
            ProductInterface::class,
            $product,
            'tax_class_id',
            $storeId
        ));

        $this->assertFalse($scopeOverriddenValue->containsValue(
            ProductInterface::class,
            $product,
            'status',
            $storeId
        ));
    }
}
