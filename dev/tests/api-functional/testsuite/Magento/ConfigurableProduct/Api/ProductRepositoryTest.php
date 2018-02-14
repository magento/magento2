<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ProductRepositoryTest for testing ConfigurableProduct integration
 */
class ProductRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';
    const CONFIGURABLE_PRODUCT_SKU = 'configurable-product-sku';

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Entity\Attribute
     */
    protected $configurableAttribute;

    /**
     * Execute per test initialization
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->eavConfig = $this->objectManager->get('Magento\Eav\Model\Config');
    }

    /**
     * Execute per test cleanup
     */
    public function tearDown()
    {
        $this->deleteProductBySku(self::CONFIGURABLE_PRODUCT_SKU);
        parent::tearDown();
    }

    protected function getConfigurableAttributeOptions()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $optionCollection */
        $optionCollection = $this->objectManager->create(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection'
        );
        $options = $optionCollection->setAttributeFilter($this->configurableAttribute->getId())->getData();
        return $options;
    }

    protected function createConfigurableProduct()
    {
        $productId1 = 10;
        $productId2 = 20;

        $label = "color";

        $this->configurableAttribute = $this->eavConfig->getAttribute('catalog_product', 'test_configurable');
        $this->assertNotNull($this->configurableAttribute);

        $options = $this->getConfigurableAttributeOptions();
        $this->assertEquals(2, count($options));

        $configurableProductOptions = [
            [
                "attribute_id" =>  $this->configurableAttribute->getId(),
                "label" => $label,
                "position" => 0,
                "values" => [
                    [
                        "value_index" =>  $options[0]['option_id'],
                    ],
                    [
                        "value_index" =>  $options[1]['option_id'],
                    ]
                ],
            ]
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
        $this->assertEquals(1, count($resultConfigurableProductOptions));
        $this->assertTrue(isset($resultConfigurableProductOptions[0]['label']));
        $this->assertTrue(isset($resultConfigurableProductOptions[0]['id']));
        $this->assertEquals($label, $resultConfigurableProductOptions[0]['label']);
        $this->assertTrue(
            isset($resultConfigurableProductOptions[0]['values'])
        );
        $this->assertEquals(2, count($resultConfigurableProductOptions[0]['values']));

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"])
        );
        $resultConfigurableProductLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"];
        $this->assertEquals(2, count($resultConfigurableProductLinks));

        $this->assertEquals([$productId1, $productId2], $resultConfigurableProductLinks);
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
        $this->assertEquals(0, count($resultConfigurableProductOptions));

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"])
        );
        $resultConfigurableProductLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"];
        $this->assertEquals(0, count($resultConfigurableProductLinks));

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
        $this->assertEquals(1, count($resultConfigurableProductOptions));

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
        $this->assertEquals(1, count($resultConfigurableProductOptions));
        //Since one product is removed, the available values for the option is reduced
        $this->assertEquals(1, count($resultConfigurableProductOptions[0]['values']));

        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"])
        );
        $resultConfigurableProductLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["configurable_product_links"];
        $this->assertEquals(1, count($resultConfigurableProductLinks));
        $this->assertEquals([$productId1], $resultConfigurableProductLinks);

        //adding back the product links, the option value should be restored
        unset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options']);
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
            $productId1, $nonExistingId
        ];

        $expectedMessage = 'Unable to save product';
        try {
            $this->saveProduct($response);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
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
            ]
        ];
        $this->saveProduct($product2);

        //leave existing option untouched
        unset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_options']);
        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]['configurable_product_links'] = [
            $productId1, $productId2
        ];

        $expectedMessage = 'Products "%1" and "%2" have the same set of attribute values.';
        try {
            $this->saveProduct($response);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
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
            $productId1, $productId2
        ];

        $expectedMessage = 'Unable to save product';
        try {
            $this->saveProduct($response);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
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
