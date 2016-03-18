<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Bundle\Api\Data\LinkInterface;

/**
 * Class ProductServiceTest for testing Bundle Product API
 */
class ProductServiceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';
    const BUNDLE_PRODUCT_ID = 'sku-test-product-bundle';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * Execute per test initialization
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productCollection = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\Collection');
    }

    /**
     * Execute per test cleanup
     */
    public function tearDown()
    {
        $this->deleteProductBySku(self::BUNDLE_PRODUCT_ID);
        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     */
    public function testCreateBundle()
    {
        $bundleProductOptions = [
            [
                "title" => "test option",
                "type" => "checkbox",
                "required" => true,
                "product_links" => [
                    [
                        "sku" => 'simple',
                        "qty" => 1,
                        'is_default' => false,
                        'price' => 1.0,
                        'price_type' => LinkInterface::PRICE_TYPE_FIXED,
                    ],
                ],
            ],
        ];

        $product = [
            "sku" => self::BUNDLE_PRODUCT_ID,
            "name" => self::BUNDLE_PRODUCT_ID,
            "type_id" => "bundle",
            "price" => 50,
            'attribute_set_id' => 4,
            "custom_attributes" => [
                [
                    "attribute_code" => "price_type",
                    "value" => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED,
                ],
                [
                    "attribute_code" => "price_view",
                    "value" => 1,
                ],
            ],
            "extension_attributes" => [
                "bundle_product_options" => $bundleProductOptions,
            ],
        ];

        $response = $this->createProduct($product);

        $this->assertEquals(self::BUNDLE_PRODUCT_ID, $response[ProductInterface::SKU]);
        $this->assertEquals(50, $response['price']);
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"])
        );
        $resultBundleProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"];
        $this->assertTrue(isset($resultBundleProductOptions[0]["product_links"][0]["sku"]));
        $this->assertEquals('simple', $resultBundleProductOptions[0]["product_links"][0]["sku"]);

        $response = $this->getProduct(self::BUNDLE_PRODUCT_ID);
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"])
        );
        $resultBundleProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"];
        $this->assertTrue(isset($resultBundleProductOptions[0]["product_links"][0]["sku"]));
        $this->assertEquals('simple', $resultBundleProductOptions[0]["product_links"][0]["sku"]);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @magentoApiDataFixture Magento/Catalog/_files/second_product_simple.php
     */
    public function testUpdateBundleModifyExistingSelection()
    {
        $bundleProduct = $this->createFixedPriceBundleProduct();
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

        $updatedProduct = $this->saveProduct($bundleProduct);

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
        $bundleProduct = $this->createFixedPriceBundleProduct();
        $bundleProductOptions = $this->getBundleProductOptions($bundleProduct);

        $existingSelectionId = $bundleProductOptions[0]['product_links'][0]['id'];

        //Change the type of existing option
        $bundleProductOptions[0]['type'] = 'select';
        //unset product_links attribute
        unset($bundleProductOptions[0]['product_links']);
        $this->setBundleProductOptions($bundleProduct, $bundleProductOptions);

        $updatedProduct = $this->saveProduct($bundleProduct);

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
        $bundleProduct = $this->createFixedPriceBundleProduct();
        $bundleProductOptions = $this->getBundleProductOptions($bundleProduct);

        $existingSelectionId = $bundleProductOptions[0]['product_links'][0]['id'];

        //unset bundle_product_options
        unset($bundleProductOptions[0]['product_links']);
        $this->setBundleProductOptions($bundleProduct, null);

        $updatedProduct = $this->saveProduct($bundleProduct);

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
        $bundleProduct = $this->createDynamicBundleProduct();
        $bundleProductOptions = $this->getBundleProductOptions($bundleProduct);

        //Add a selection to existing option
        $bundleProductOptions[0]['product_links'][] = [
            'sku' => 'simple2',
            'qty' => 2,
            "price" => 20,
            "price_type" => 1,
            "is_default" => false,
        ];
        $this->setBundleProductOptions($bundleProduct, $bundleProductOptions);
        $updatedProduct = $this->saveProduct($bundleProduct);

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
        $bundleProduct = $this->createDynamicBundleProduct();

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
                    "price" => 20,
                    "price_type" => 1,
                    "is_default" => false,
                ],
            ],
        ];
        $this->setBundleProductOptions($bundleProduct, $bundleProductOptions);
        $this->saveProduct($bundleProduct);

        $updatedProduct = $this->getProduct(self::BUNDLE_PRODUCT_ID);
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
     * @param array $product
     * @return array|null
     */
    protected function getBundleProductOptions($product)
    {
        if (isset($product[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"])) {
            return $product[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"];
        } else {
            return null;
        }
    }

    /**
     * Set the bundle_product_options custom attribute, replace existing attribute if exists
     *
     * @param array $product
     * @param array $bundleProductOptions
     */
    protected function setBundleProductOptions(&$product, $bundleProductOptions)
    {
        $product["extension_attributes"]["bundle_product_options"] = $bundleProductOptions;
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
            [
                "title" => "test option",
                "type" => "checkbox",
                "required" => 1,
                "product_links" => [
                    [
                        "sku" => 'simple',
                        "qty" => 1,
                        "is_default" => true,
                        "price" => 10,
                        "price_type" => 1,
                    ],
                ],
            ],
        ];

        $uniqueId = self::BUNDLE_PRODUCT_ID;
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
                "price_view" => [
                    "attribute_code" => "price_view",
                    "value" => "1",
                ],
            ],
            "extension_attributes" => [
                "bundle_product_options" => $bundleProductOptions,
            ],
        ];

        $response = $this->createProduct($product);
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"])
        );
        $resultBundleProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"];
        $this->assertTrue(isset($resultBundleProductOptions[0]["product_links"][0]["sku"]));
        $this->assertEquals('simple', $resultBundleProductOptions[0]["product_links"][0]["sku"]);
        $this->assertTrue(isset($response['custom_attributes']));
        $customAttributes = $this->convertCustomAttributes($response['custom_attributes']);
        $this->assertTrue(isset($customAttributes['price_type']));
        $this->assertEquals(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC, $customAttributes['price_type']);
        $this->assertTrue(isset($customAttributes['price_view']));
        $this->assertEquals(1, $customAttributes['price_view']);
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
                        "is_default" => true,
                    ],
                ],
            ],
        ];

        $uniqueId = self::BUNDLE_PRODUCT_ID;
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
                "price_view" => [
                    "attribute_code" => "price_view",
                    "value" => "1",
                ],
            ],
            "extension_attributes" => [
                "bundle_product_options" => $bundleProductOptions,
            ],
        ];

        $response = $this->createProduct($product);
        $resultBundleProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"];
        $this->assertEquals('simple', $resultBundleProductOptions[0]["product_links"][0]["sku"]);
        $this->assertTrue(isset($response['custom_attributes']));
        $customAttributes = $this->convertCustomAttributes($response['custom_attributes']);
        $this->assertTrue(isset($customAttributes['price_type']));
        $this->assertEquals(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED, $customAttributes['price_type']);
        $this->assertTrue(isset($customAttributes['price_view']));
        $this->assertEquals(1, $customAttributes['price_view']);
        return $response;
    }

    protected function convertCustomAttributes($customAttributes)
    {
        $convertedCustomAttribute = [];
        foreach ($customAttributes as $customAttribute) {
            $convertedCustomAttribute[$customAttribute['attribute_code']] = $customAttribute['value'];
        }
        return $convertedCustomAttribute;
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['sku' => $productSku]) : $this->_webApiCall($serviceInfo);

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
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }

    /**
     * Delete a product by sku
     *
     * @param $productSku
     * @return bool
     */
    protected function deleteProductBySku($productSku)
    {
        $resourcePath = self::RESOURCE_PATH . '/' . $productSku;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteById',
            ],
        ];
        $requestData = ["sku" => $productSku];
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
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }
}
