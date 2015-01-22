<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

/**
 * Class ProductServiceTest for testing Bundle Product API
 */
class ProductServiceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';
    const UNIQUE_ID = 'sku-test-product-bundle';

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $productCollection;

    /**
     * Execute per test initialization
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productCollection = $objectManager->get('Magento\Catalog\Model\Resource\Product\Collection');
    }

    /**
     * Execute per test cleanup
     */
    public function tearDown()
    {
        $this->deleteProduct(self::UNIQUE_ID);
        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     */
    public function testCreateBundle()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }

        $response = $this->createDynamicBundleProduct();
        $this->assertEquals(self::UNIQUE_ID, $response[ProductInterface::SKU]);

        $response = $this->getProduct(self::UNIQUE_ID);
        $bundleProductOptions = $this->getBundleProductOptions($response);
        $this->assertNotNull($bundleProductOptions, 'bundle_product_options custom attribute not found');
        $this->assertEquals('simple', $bundleProductOptions[0]["product_links"][0]["sku"]);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testUpdateBundleModifyExistingSelection()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }

        $this->createFixedPriceBundleProduct();
        $bundleProduct = $this->getProduct(self::UNIQUE_ID);

        $bundleProductOptions = $this->getBundleProductOptions($bundleProduct);

        $existingSelectionId = $bundleProductOptions[0]['product_links'][0]['id'];

        //Change the type of existing option
        $bundleProductOptions[0]['type'] = 'select';
        //Change the sku of existing link and qty
        $bundleProductOptions[0]['product_links'][0]['sku'] = 'simple2';
        $bundleProductOptions[0]['product_links'][0]['qty'] = 2;
        $bundleProductOptions[0]['product_links'][0]['price'] = 10;
        $bundleProductOptions[0]['product_links'][0]['price_type'] = 1;
        $this->setBundleProductOptions($bundleProduct, $bundleProductOptions);

        $this->saveProduct($bundleProduct);

        $updatedProduct = $this->getProduct(self::UNIQUE_ID);
        $bundleOptions = $this->getBundleProductOptions($updatedProduct);
        $this->assertEquals('select', $bundleOptions[0]['type']);
        $this->assertEquals('simple2', $bundleOptions[0]['product_links'][0]['sku']);
        $this->assertEquals(2, $bundleOptions[0]['product_links'][0]['qty']);
        $this->assertEquals($existingSelectionId, $bundleOptions[0]['product_links'][0]['id']);
        $this->assertEquals(10, $bundleOptions[0]['product_links'][0]['price']);
        $this->assertEquals(1, $bundleOptions[0]['product_links'][0]['price_type']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testUpdateBundleModifyExistingOptionOnly()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }

        $this->createFixedPriceBundleProduct();
        $bundleProduct = $this->getProduct(self::UNIQUE_ID);

        $bundleProductOptions = $this->getBundleProductOptions($bundleProduct);

        $existingSelectionId = $bundleProductOptions[0]['product_links'][0]['id'];

        //Change the type of existing option
        $bundleProductOptions[0]['type'] = 'select';
        //unset product_links attribute
        unset($bundleProductOptions[0]['product_links']);
        $this->setBundleProductOptions($bundleProduct, $bundleProductOptions);

        $this->saveProduct($bundleProduct);

        $updatedProduct = $this->getProduct(self::UNIQUE_ID);
        $bundleOptions = $this->getBundleProductOptions($updatedProduct);
        $this->assertEquals('select', $bundleOptions[0]['type']);
        $this->assertEquals('simple', $bundleOptions[0]['product_links'][0]['sku']);
        $this->assertEquals(1, $bundleOptions[0]['product_links'][0]['qty']);
        $this->assertEquals($existingSelectionId, $bundleOptions[0]['product_links'][0]['id']);
        $this->assertEquals(20, $bundleOptions[0]['product_links'][0]['price']);
        $this->assertEquals(1, $bundleOptions[0]['product_links'][0]['price_type']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testUpdateProductWithoutBundleOptions()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }

        $this->createFixedPriceBundleProduct();
        $bundleProduct = $this->getProduct(self::UNIQUE_ID);

        $bundleProductOptions = $this->getBundleProductOptions($bundleProduct);

        $existingSelectionId = $bundleProductOptions[0]['product_links'][0]['id'];

        //unset bundle_product_options
        unset($bundleProductOptions[0]['product_links']);
        $this->setBundleProductOptions($bundleProduct, null);

        $this->saveProduct($bundleProduct);

        $updatedProduct = $this->getProduct(self::UNIQUE_ID);
        $bundleOptions = $this->getBundleProductOptions($updatedProduct);
        $this->assertEquals('checkbox', $bundleOptions[0]['type']);
        $this->assertEquals('simple', $bundleOptions[0]['product_links'][0]['sku']);
        $this->assertEquals(1, $bundleOptions[0]['product_links'][0]['qty']);
        $this->assertEquals($existingSelectionId, $bundleOptions[0]['product_links'][0]['id']);
        $this->assertEquals(20, $bundleOptions[0]['product_links'][0]['price']);
        $this->assertEquals(1, $bundleOptions[0]['product_links'][0]['price_type']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testUpdateBundleAddSelection()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }

        $this->createDynamicBundleProduct();
        $bundleProduct = $this->getProduct(self::UNIQUE_ID);

        $bundleProductOptions = $this->getBundleProductOptions($bundleProduct);

        //Add a selection to existing option
        $bundleProductOptions[0]['product_links'][] = [
            'sku' => 'simple2',
            'qty' => 2,
        ];
        $this->setBundleProductOptions($bundleProduct, $bundleProductOptions);
        $this->saveProduct($bundleProduct);

        $updatedProduct = $this->getProduct(self::UNIQUE_ID);
        $bundleOptions = $this->getBundleProductOptions($updatedProduct);
        $this->assertEquals('simple', $bundleOptions[0]['product_links'][0]['sku']);
        $this->assertEquals('simple2', $bundleOptions[0]['product_links'][1]['sku']);
        $this->assertEquals(2, $bundleOptions[0]['product_links'][1]['qty']);
        $this->assertGreaterThan(
            $bundleOptions[0]['product_links'][0]['id'],
            $bundleOptions[0]['product_links'][1]['id']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testUpdateBundleAddAndDeleteOption()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }

        $this->createDynamicBundleProduct();
        $bundleProduct = $this->getProduct(self::UNIQUE_ID);

        $bundleProductOptions = $this->getBundleProductOptions($bundleProduct);

        $oldOptionId = $bundleProductOptions[0]['option_id'];
        //replace current option with a new option
        $bundleProductOptions[0] = [
            'title' => 'new option',
            'required' => true,
            'type' => 'select',
            'product_links' => [
                [
                    'sku' => 'simple2',
                    'qty' => 2,
                ],
            ],
        ];
        $this->setBundleProductOptions($bundleProduct, $bundleProductOptions);
        $this->saveProduct($bundleProduct);

        $updatedProduct = $this->getProduct(self::UNIQUE_ID);
        $bundleOptions = $this->getBundleProductOptions($updatedProduct);
        $this->assertEquals('new option', $bundleOptions[0]['title']);
        $this->assertTrue($bundleOptions[0]['required']);
        $this->assertEquals('select', $bundleOptions[0]['type']);
        $this->assertGreaterThan($oldOptionId, $bundleOptions[0]['option_id']);
        $this->assertFalse(isset($bundleOptions[1]));
        $this->assertEquals('simple2', $bundleOptions[0]['product_links'][0]['sku']);
        $this->assertEquals(2, $bundleOptions[0]['product_links'][0]['qty']);
    }

    /**
     * Get the bundle_product_options custom attribute from product, null if the attribute is not set
     *
     * @param $product
     * @return array|null
     */
    protected function getBundleProductOptions($product)
    {
        foreach ($product[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY] as $customAttribute) {
            if ($customAttribute["attribute_code"] === 'bundle_product_options') {
                return $customAttribute["value"];
            }
        }
        return null;
    }

    /**
     * Set the bundle_product_options custom attribute, replace existing attribute if exists
     *
     * @param array $product
     * @param array $bundleProductOptions
     */
    protected function setBundleProductOptions(&$product, $bundleProductOptions)
    {
        foreach ($product[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY] as &$customAttribute) {
            if ($customAttribute["attribute_code"] === 'bundle_product_options') {
                $customAttribute["value"] = $bundleProductOptions;
                return;
            }
        }
        $product['AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY'][] = [
            'attribute_code' => 'bundle_product_options',
            'value' => $bundleProductOptions,
        ];
        return;
    }

    /**
     * Create dynamic bundle product with one option
     *
     * @return array
     */
    protected function createDynamicBundleProduct()
    {
        $bundleProductOptions = [
            "attribute_code" => "bundle_product_options",
            "value" => [
                [
                    "title" => "test option",
                    "type" => "checkbox",
                    "required" => 1,
                    "product_links" => [
                        [
                            "sku" => 'simple',
                            "qty" => 1,
                        ],
                    ],
                ],
            ],
        ];

        $uniqueId = self::UNIQUE_ID;
        $product = [
            "sku" => $uniqueId,
            "name" => $uniqueId,
            "type_id" => "bundle",
            'attribute_set_id' => 4,
            "custom_attributes" => [
                "price_type" => [
                    'attribute_code' => 'price_type',
                    'value' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
                ],
                "bundle_product_options" => $bundleProductOptions,
                "price_view" => [
                    "attribute_code" => "price_view",
                    "value" => "test",
                ],
            ],
        ];

        $response = $this->createProduct($product);
        $this->assertEquals(
            $bundleProductOptions,
            $response[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY]["bundle_product_options"]
        );
        return $response;
    }

    /**
     * Create fixed price bundle product with one option
     *
     * @return array
     */
    protected function createFixedPriceBundleProduct()
    {
        $bundleProductOptions = [
            "attribute_code" => "bundle_product_options",
            "value" => [
                [
                    "title" => "test option",
                    "type" => "checkbox",
                    "required" => 1,
                    "product_links" => [
                        [
                            "sku" => 'simple',
                            "qty" => 1,
                            "price" => 20,
                            "price_type" => 1,
                        ],
                    ],
                ],
            ],
        ];

        $uniqueId = self::UNIQUE_ID;
        $product = [
            "sku" => $uniqueId,
            "name" => $uniqueId,
            "type_id" => "bundle",
            "price" => 50,
            'attribute_set_id' => 4,
            "custom_attributes" => [
                "price_type" => [
                    'attribute_code' => 'price_type',
                    'value' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED
                ],
                "bundle_product_options" => $bundleProductOptions,
                "price_view" => [
                    "attribute_code" => "price_view",
                    "value" => "test",
                ],
            ],
        ];

        $response = $this->createProduct($product);
        $this->assertEquals(
            $bundleProductOptions,
            $response[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY]["bundle_product_options"]
        );
        return $response;
    }

    /**
     * Get product
     *
     * @param string $productSku
     * @return array the product data
     */
    protected function getProduct($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['productSku' => $productSku]) : $this->_webApiCall($serviceInfo);

        return $response;
    }

    /**
     * Create product
     *
     * @param array $product
     * @return array the created product data
     */
    protected function createProduct($product)
    {
        $serviceInfo = [
            'rest' => ['resourcePath' => self::RESOURCE_PATH, 'httpMethod' => RestConfig::HTTP_METHOD_POST],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $product[ProductInterface::SKU] = $response[ProductInterface::SKU];
        return $product;
    }

    /**
     * Delete a product by sku
     *
     * @param $productSku
     * @return array|bool|float|int|string
     */
    protected function deleteProduct($productSku)
    {
        $resourcePath = self::RESOURCE_PATH . '/' . $productSku;
        $serviceInfo = [
            'rest' => ['resourcePath' => $resourcePath, 'httpMethod' => RestConfig::HTTP_METHOD_DELETE],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = [];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }

    /**
     * Save product
     *
     * @param array $product
     * @return array the created product data
     */
    protected function saveProduct($product)
    {
        $resourcePath = self::RESOURCE_PATH . '/' . $product['sku'];
        $serviceInfo = [
            'rest' => ['resourcePath' => $resourcePath, 'httpMethod' => RestConfig::HTTP_METHOD_PUT],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $product[ProductInterface::SKU] = $response[ProductInterface::SKU];
        return $product;
    }
}
