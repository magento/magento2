<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
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
    private const SERVICE_NAME = 'catalogProductRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products';

    /**
     * @var array
     */
    private $fixtureProducts = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
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

    #[
        Config(Data::XML_PATH_PRICE_SCOPE, Data::PRICE_SCOPE_WEBSITE),
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store3'),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id$' ]], as: 'product'),
    ]
    public function testUpdatePrice(): void
    {
        $store = $this->objectManager->get(Store::class);
        $defaultWebsiteStore1 = $store->load('default', 'code')->getCode();
        $secondWebsiteStore1 = $this->fixtures->get('store2')->getCode();
        $secondWebsiteStore2 = $this->fixtures->get('store3')->getCode();
        $sku = $this->fixtures->get('product')->getSku();

        // change any attribute value in second store
        $request = [
            ProductInterface::SKU => $sku,
            'name' => 'updated product name for storeview'
        ];
        $this->saveProduct($request, $secondWebsiteStore1);

        // now update prices in second website
        $request = [
            ProductInterface::SKU => $sku,
            'price' => 9,
            ProductInterface::CUSTOM_ATTRIBUTES => [
                [
                    'attribute_code' => 'special_price',
                    'value' => 8,
                ]
            ],
        ];
        $this->saveProduct($request, $secondWebsiteStore1);
        $defaultWebsiteStore1Response = $this->flattenCustomAttributes($this->getProduct($sku, $defaultWebsiteStore1));
        $this->assertEquals(10, $defaultWebsiteStore1Response['price']);
        $this->assertArrayNotHasKey('special_price', $defaultWebsiteStore1Response);
        $secondWebsiteStore1Response = $this->flattenCustomAttributes($this->getProduct($sku, $secondWebsiteStore1));
        $this->assertEquals(9, $secondWebsiteStore1Response['price']);
        $this->assertEquals(8, $secondWebsiteStore1Response['special_price']);
        $secondWebsiteStore2Response = $this->flattenCustomAttributes($this->getProduct($sku, $secondWebsiteStore2));
        $this->assertEquals(9, $secondWebsiteStore2Response['price']);
        $this->assertEquals(8, $secondWebsiteStore2Response['special_price']);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_with_image.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPartialUpdateShouldNotOverrideImagesRolesInheritance(): void
    {
        $sku = 'simple';
        $name = 'Product Simple edited';
        $store = $this->objectManager->get(Store::class);
        $storeId = (int) $store->load('fixture_third_store', 'code')->getId();
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get($sku);
        $request = [
            ProductInterface::SKU => $sku,
            ProductInterface::NAME => $name,
        ];
        $response = $this->saveProduct($request, 'fixture_third_store');
        $this->assertEquals($name, $response['name']);
        $this->assertOverriddenValues(
            [
                'name' => true,
                'image' => false,
                'small_image' => false,
                'thumbnail' => false,
            ],
            $product,
            $storeId
        );
    }

    #[
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
    ]
    public function testPartialUpdateShouldNotOverrideUrlKeyInheritance(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $store = $this->objectManager->get(Store::class);
        $defaultStore = $store->load('default', 'code');
        $sku2 = $this->fixtures->get('product2')->getSku();
        $sku1Name = $this->fixtures->get('product1')->getName();
        $sku2NewName = $this->fixtures->get('product2')->getName() . ' storeview';
        $sku2UrlKey = $this->fixtures->get('product2')->getUrlKey();

        // Change the second product name with the first product name in default store view
        $this->saveProduct(
            [
                ProductInterface::SKU => $sku2,
                'name' => $sku1Name
            ],
            $defaultStore->getCode()
        );
        $response = $this->flattenCustomAttributes($this->getProduct($sku2, $defaultStore->getCode()));
        $this->assertEquals($sku1Name, $response['name']);
        // Assert that Url Key has not changed
        $this->assertEquals($sku2UrlKey, $response['url_key']);
        $product = $productRepository->get($sku2, false, $defaultStore->getId(), true);
        $this->assertOverriddenValues(
            [
                'name' => true,
                'url_key' => false,
            ],
            $product,
            (int) $defaultStore->getId()
        );

        // Change the second product name with a new name in default store view
        $this->saveProduct(
            [
                ProductInterface::SKU => $sku2,
                'name' => $sku2NewName
            ],
            $defaultStore->getCode()
        );
        $response = $this->flattenCustomAttributes($this->getProduct($sku2, $defaultStore->getCode()));
        $this->assertEquals($sku2NewName, $response['name']);
        // Assert that Url Key has not changed
        $this->assertEquals($sku2UrlKey, $response['url_key']);
        $product = $productRepository->get($sku2, false, $defaultStore->getId(), true);
        $this->assertOverriddenValues(
            [
                'name' => true,
                'url_key' => false,
            ],
            $product,
            (int) $defaultStore->getId()
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

    /**
     * @param array $data
     * @return array
     */
    private function flattenCustomAttributes(array $data): array
    {
        $customAttributes = $data[ProductInterface::CUSTOM_ATTRIBUTES] ?? [];
        unset($data[ProductInterface::CUSTOM_ATTRIBUTES]);
        return array_merge(array_column($customAttributes, 'value', 'attribute_code'), $data);
    }

    /**
     * Get product
     *
     * @param string $sku
     * @param string|null $storeCode
     * @return array|bool|float|int|string
     */
    private function getProduct($sku, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        return $this->_webApiCall($serviceInfo, ['sku' => $sku], null, $storeCode);
    }
}
