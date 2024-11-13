<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ProductRepositoryTest for testing ConfigurableProduct integration
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';
    const CONFIGURABLE_PRODUCT_SKU = 'configurable-product-sku';

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Attribute
     */
    private $configurableAttribute;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->eavConfig = $this->objectManager->get(Config::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->deleteProductBySku(self::CONFIGURABLE_PRODUCT_SKU);
        parent::tearDown();
    }

    /**
     * Retrieve configurable attribute options
     *
     * @return array
     */
    protected function getConfigurableAttributeOptions()
    {
        /** @var Collection $optionCollection */
        $optionCollection = $this->objectManager->create(
            Collection::class
        );
        $options = $optionCollection->setAttributeFilter($this->configurableAttribute->getId())->getData();
        return $options;
    }

    /**
     * Create configurable product by web api
     *
     * @return array
     */
    protected function createConfigurableProduct()
    {
        $productId1 = 10;
        $productId2 = 20;

        $label = "color";

        $this->configurableAttribute = $this->eavConfig->getAttribute('catalog_product', 'test_configurable');
        $this->assertNotNull($this->configurableAttribute);

        $options = $this->getConfigurableAttributeOptions();
        $this->assertCount(2, $options);

        $configurableProductOptions = [
            [
                "attribute_id" => $this->configurableAttribute->getId(),
                "label" => $label,
                "position" => 0,
                "values" => [
                    [
                        "value_index" => $options[0]['option_id'],
                    ],
                    [
                        "value_index" => $options[1]['option_id'],
                    ],
                ],
            ],
        ];

        $product = [
            "sku" => self::CONFIGURABLE_PRODUCT_SKU,
            "name" => self::CONFIGURABLE_PRODUCT_SKU,
            "type_id" => "configurable",
            "price" => 50,
            'attribute_set_id' => 4,
            "custom_attributes" => [
                [
                    "attribute_code" => $this->configurableAttribute->getAttributeCode(),
                    "value" => $options[0]['option_id'],
                ],
            ],
            "extension_attributes" => [
                "configurable_product_options" => $configurableProductOptions,
                "configurable_product_links" => [$productId1, $productId2],
            ],
        ];

        $response = $this->createProduct($product);
        return $response;
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testCreateConfigurableProduct()
    {
        $productId1 = 10;
        $productId2 = 20;
        $label = "color";

        $response = $this->createConfigurableProduct();
        $this->assertEquals(self::CONFIGURABLE_PRODUCT_SKU, $response[ProductInterface::SKU]);
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"])
        );
        $resultConfigurableProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"];
        $this->assertCount(1, $resultConfigurableProductOptions);
        $this->assertTrue(isset($resultConfigurableProductOptions[0]['label']));
        $this->assertTrue(isset($resultConfigurableProductOptions[0]['id']));
        $this->assertEquals($label, $resultConfigurableProductOptions[0]['label']);
        $this->assertTrue(
            isset($resultConfigurableProductOptions[0]['values'])
        );
        $this->assertCount(2, $resultConfigurableProductOptions[0]['values']);

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"])
        );
        $resultConfigurableProductLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"];
        $this->assertCount(2, $resultConfigurableProductLinks);

        $this->assertEquals([$productId1, $productId2], $resultConfigurableProductLinks);
    }

    /**
     * Verify configurable product creation passes validation with required attribute not specified in product itself.
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testCreateConfigurableProductWithRequiredAttribute(): void
    {
        $configurableAttribute = $this->eavConfig->getAttribute('catalog_product', 'test_configurable');
        $configurableAttribute->setIsRequired(true);
        $configurableAttribute->save();
        $response = $this->createConfigurableProductWithRequiredAttribute();
        $this->assertEquals(self::CONFIGURABLE_PRODUCT_SKU, $response[ProductInterface::SKU]);
    }

    /**
     * Create configurable with simple which has zero attribute value
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_attribute_with_source_model.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testCreateConfigurableProductWithZeroOptionValue(): void
    {
        $attributeCode = 'test_configurable_with_sm';
        $attributeValue = 0;

        $product = $this->productRepository->get('simple');
        $product->setCustomAttribute($attributeCode, $attributeValue);
        $this->productRepository->save($product);

        $configurableAttribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

        $productData = [
            'sku' => self::CONFIGURABLE_PRODUCT_SKU,
            'name' => self::CONFIGURABLE_PRODUCT_SKU,
            'type_id' => Configurable::TYPE_CODE,
            'attribute_set_id' => 4,
            'extension_attributes' => [
                'configurable_product_options' => [
                    [
                        'attribute_id' => $configurableAttribute->getId(),
                        'label' => 'Test configurable with source model',
                        'values' => [
                            ['value_index' => '0'],
                        ],
                    ],
                ],
                'configurable_product_links' => [$product->getId()],
            ],
        ];

        $response = $this->createProduct($productData);

        $this->assertArrayHasKey(ProductInterface::SKU, $response);
        $this->assertEquals(self::CONFIGURABLE_PRODUCT_SKU, $response[ProductInterface::SKU]);

        $this->assertArrayHasKey(ProductInterface::TYPE_ID, $response);
        $this->assertEquals('configurable', $response[ProductInterface::TYPE_ID]);

        $this->assertArrayHasKey(ProductInterface::EXTENSION_ATTRIBUTES_KEY, $response);
        $this->assertArrayHasKey(
            'configurable_product_options',
            $response[ProductInterface::EXTENSION_ATTRIBUTES_KEY]
        );
        $configurableProductOption =
            current($response[ProductInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options']);

        $this->assertArrayHasKey('attribute_id', $configurableProductOption);
        $this->assertEquals($configurableAttribute->getId(), $configurableProductOption['attribute_id']);
        $this->assertArrayHasKey('values', $configurableProductOption);
        $this->assertEquals($attributeValue, $configurableProductOption['values'][0]['value_index']);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testDeleteConfigurableProductOption()
    {
        $response = $this->createConfigurableProduct();
        //delete existing option
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options'] = [];
        //leave the product links unchanged
        unset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_links']);
        $response = $this->saveProduct($response);

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"])
        );
        $resultConfigurableProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"];
        $this->assertCount(0, $resultConfigurableProductOptions);

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"])
        );
        $resultConfigurableProductLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"];
        $this->assertCount(0, $resultConfigurableProductLinks);

        $this->assertEquals([], $resultConfigurableProductLinks);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testUpdateConfigurableProductOption()
    {
        $productId1 = 10;
        $newLabel = 'size';

        $response = $this->createConfigurableProduct();
        $option = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"][0];

        $optionId = $option['id'];
        $productId = $option['product_id'];
        $updatedOption = [
            'id' => $optionId,
            'attribute_id' => $option['attribute_id'],
            'label' => $newLabel,
            'position' => 1,
            'values' => [
                [
                    'value_index' => $option['values'][0]['value_index'],
                ],
            ],
            'product_id' => $productId,
        ];
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options'][0] =
            $updatedOption;
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_links'] = [$productId1];
        $response = $this->saveProduct($response);

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"])
        );
        $resultConfigurableProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"];
        $this->assertCount(1, $resultConfigurableProductOptions);

        unset($updatedOption['id']);
        unset($resultConfigurableProductOptions[0]['id']);
        $this->assertEquals($updatedOption, $resultConfigurableProductOptions[0]);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testUpdateConfigurableProductLinks()
    {
        $productId1 = 10;
        $productId2 = 20;

        $response = $this->createConfigurableProduct();
        $options = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options'];
        //leave existing option untouched
        unset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options']);
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_links'] = [$productId1];
        $response = $this->saveProduct($response);

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"])
        );
        $resultConfigurableProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_options"];
        $this->assertCount(1, $resultConfigurableProductOptions);
        //Since one product is removed, the available values for the option is reduced
        $this->assertCount(1, $resultConfigurableProductOptions[0]['values']);

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"])
        );
        $resultConfigurableProductLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"];
        $this->assertCount(1, $resultConfigurableProductLinks);
        $this->assertEquals([$productId1], $resultConfigurableProductLinks);

        //adding back the product links, the option value should be restored
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_links']
            = [$productId1, $productId2];
        //set the value for required attribute
        $response["custom_attributes"][] =
            [
                "attribute_code" => $this->configurableAttribute->getAttributeCode(),
                "value" => $resultConfigurableProductOptions[0]['values'][0]['value_index'],
            ];

        $response = $this->saveProduct($response);

        $currentOptions = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options'];

        $this->assertEquals($options, $currentOptions);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testUpdateConfigurableProductLinksWithNonExistingProduct()
    {
        $productId1 = 10;
        $nonExistingId = 999;

        $response = $this->createConfigurableProduct();
        //leave existing option untouched
        unset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options']);
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_links'] = [
            $productId1,
            $nonExistingId,
        ];

        $expectedMessage = 'The product that was requested doesn\'t exist. Verify the product and try again.';
        try {
            $this->saveProduct($response);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testUpdateConfigurableProductLinksWithDuplicateAttributes()
    {
        $productId1 = 10;
        $productId2 = 20;

        $response = $this->createConfigurableProduct();
        $options = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options'];
        //make product2 and product1 have the same value for the configurable attribute
        $optionValue1 = $options[0]['values'][0]['value_index'];
        $product2 = $this->getProduct('simple_' . $productId2);
        $product2['custom_attributes'] = [
            [
                'attribute_code' => 'test_configurable',
                'value' => $optionValue1,
            ],
        ];
        $this->saveProduct($product2);

        //leave existing option untouched
        unset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options']);
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_links'] = [
            $productId1,
            $productId2,
        ];

        $expectedMessage = 'Products "%1" and "%2" have the same set of attribute values.';
        try {
            $this->saveProduct($response);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(['0' => 20, '1' => 10], $errorObj['parameters']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testUpdateConfigurableProductLinksWithWithoutVariationAttributes()
    {
        $productId1 = 99;
        $productId2 = 88;

        $response = $this->createConfigurableProduct();

        /** delete all variation attribute */
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options'] = [];
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_links'] = [
            $productId1,
            $productId2,
        ];

        $expectedMessage = 'The product that was requested doesn\'t exist. Verify the product and try again.';
        try {
            $this->saveProduct($response);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
        }
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
                'httpMethod' => Request::HTTP_METHOD_GET,
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
                'httpMethod' => Request::HTTP_METHOD_POST,
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
                'httpMethod' => Request::HTTP_METHOD_DELETE,
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
        if (isset($product['custom_attributes'])) {
            $count = count($product['custom_attributes']);
            for ($i = 0; $i < $count; $i++) {
                if ($product['custom_attributes'][$i]['attribute_code'] == 'category_ids'
                    && !is_array($product['custom_attributes'][$i]['value'])
                ) {
                    $product['custom_attributes'][$i]['value'] = [""];
                }
            }
        }
        $resourcePath = self::RESOURCE_PATH . '/' . $product['sku'];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => Request::HTTP_METHOD_PUT,
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
     * Create configurable product with required attribute by web api.
     *
     * @return array
     */
    private function createConfigurableProductWithRequiredAttribute(): array
    {
        $this->configurableAttribute = $this->eavConfig->getAttribute('catalog_product', 'test_configurable');
        $options = $this->getConfigurableAttributeOptions();
        $configurableProductOptions = [
            [
                "attribute_id" => $this->configurableAttribute->getId(),
                "label" => 'color',
                "position" => 0,
                "values" => [
                    [
                        "value_index" => $options[0]['option_id'],
                    ],
                    [
                        "value_index" => $options[1]['option_id'],
                    ],
                ],
            ],
        ];
        $product = [
            "sku" => self::CONFIGURABLE_PRODUCT_SKU,
            "name" => self::CONFIGURABLE_PRODUCT_SKU,
            "type_id" => "configurable",
            "price" => 50,
            'attribute_set_id' => 4,
            "extension_attributes" => [
                "configurable_product_options" => $configurableProductOptions,
                "configurable_product_links" => [10, 20],
            ],
        ];

        return $this->createProduct($product);
    }
}
