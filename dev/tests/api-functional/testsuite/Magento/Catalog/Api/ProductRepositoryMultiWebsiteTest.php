<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
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

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_text_attribute.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_varchar_attribute.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPartialUpdate(): void
    {
        $this->_markTestAsRestOnly(
            'Test skipped due to known issue with SOAP. NULL value is cast to corresponding attribute type.'
        );
        $sku = 'api_test_update_product';
        $store = $this->objectManager->get(Store::class);
        $storeId = (int) $store->load('fixture_third_store', 'code')->getId();
        $this->updateAttribute('varchar_attribute', ['is_global' => ScopedAttributeInterface::SCOPE_STORE]);
        $this->updateAttribute('text_attribute', ['is_global' => ScopedAttributeInterface::SCOPE_STORE]);
        $request1 = [
            ProductInterface::SKU => $sku,
            ProductInterface::NAME => 'Test 1',
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            ProductInterface::PRICE => 10,
            ProductInterface::STATUS => 1,
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::WEIGHT => 100,
            ProductInterface::CUSTOM_ATTRIBUTES => [
                [
                    'attribute_code' => 'varchar_attribute',
                    'value' => 'api_test_value_varchar',
                ],
                [
                    'attribute_code' => 'text_attribute',
                    'value' => 'api_test_value_text',
                ]
            ],
        ];
        $response = $this->saveProduct($request1, 'all');
        $this->assertResponse($request1, $response);
        $this->fixtureProducts[] = $sku;
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku);
        $request2 = [
            ProductInterface::SKU => $sku,
            ProductInterface::CUSTOM_ATTRIBUTES => [
                [
                    'attribute_code' => 'varchar_attribute',
                    'value' => 'api_test_value_varchar_changed',
                ]
            ],
        ];
        $response2 = $this->saveProduct($request2, 'fixture_third_store');
        $expected = [
            'varchar_attribute' => 'api_test_value_varchar_changed',
            'text_attribute' => 'api_test_value_text'
        ];
        $this->assertResponse(
            array_merge($request1, $expected),
            $response2
        );
        $this->assertOverriddenValues(
            [
                'visibility' => false,
                'tax_class_id' => false,
                'status' => false,
                'name' => false,
                'varchar_attribute' => true,
                'text_attribute' => false,
            ],
            $product,
            $storeId
        );
        $request3 = [
            ProductInterface::SKU => $sku,
            ProductInterface::CUSTOM_ATTRIBUTES => [
                [
                    'attribute_code' => 'text_attribute',
                    'value' => 'api_test_value_text_changed',
                ]
            ],
        ];
        $response3 = $this->saveProduct($request3, 'fixture_third_store');
        $expected = [
            'varchar_attribute' => 'api_test_value_varchar_changed',
            'text_attribute' => 'api_test_value_text_changed'
        ];
        $this->assertResponse(
            array_merge($request1, $expected),
            $response3
        );
        $this->assertOverriddenValues(
            [
                'visibility' => false,
                'tax_class_id' => false,
                'status' => false,
                'name' => false,
                'varchar_attribute' => true,
                'text_attribute' => true,
            ],
            $product,
            $storeId
        );
        $request4 = [
            ProductInterface::SKU => $sku,
            ProductInterface::CUSTOM_ATTRIBUTES => [
                [
                    'attribute_code' => 'text_attribute',
                    'value' => null,
                ]
            ],
        ];
        $response4 = $this->saveProduct($request4, 'fixture_third_store');
        $expected = [
            'varchar_attribute' => 'api_test_value_varchar_changed',
            'text_attribute' => 'api_test_value_text'
        ];
        $this->assertResponse(
            array_merge($request1, $expected),
            $response4
        );
        $this->assertOverriddenValues(
            [
                'visibility' => false,
                'tax_class_id' => false,
                'status' => false,
                'name' => false,
                'varchar_attribute' => true,
                'text_attribute' => false,
            ],
            $product,
            $storeId
        );
    }

    /**
     * @param array $expected
     * @param array $actual
     */
    private function assertResponse(array $expected, array $actual): void
    {
        $customAttributes = $expected[ProductInterface::CUSTOM_ATTRIBUTES] ?? [];
        unset($expected[ProductInterface::CUSTOM_ATTRIBUTES]);
        $expected = array_merge(array_column($customAttributes, 'value', 'attribute_code'), $expected);

        $customAttributes = $actual[ProductInterface::CUSTOM_ATTRIBUTES] ?? [];
        unset($actual[ProductInterface::CUSTOM_ATTRIBUTES]);
        $actual = array_merge(array_column($customAttributes, 'value', 'attribute_code'), $actual);

        $this->assertEquals($expected, array_intersect_key($actual, $expected));
    }

    /**
     * @param Product $product
     * @param array $expected
     * @param int $storeId
     */
    private function assertOverriddenValues(array $expected, Product $product, int $storeId): void
    {
        /** @var ScopeOverriddenValue $scopeOverriddenValue */
        $scopeOverriddenValue = $this->objectManager->create(ScopeOverriddenValue::class);
        $actual = [];
        foreach (array_keys($expected) as $attribute) {
            $actual[$attribute] = $scopeOverriddenValue->containsValue(
                ProductInterface::class,
                $product,
                $attribute,
                $storeId
            );
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * Update attribute
     *
     * @param string $code
     * @param array $data
     * @return void
     */
    private function updateAttribute(string $code, array $data): void
    {
        $attributeRepository = $this->objectManager->create(ProductAttributeRepositoryInterface::class);
        $attribute = $attributeRepository->get($code);
        $attribute->addData($data);
        $attributeRepository->save($attribute);
    }
}
