<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Api;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class LinkManagementTest for testing ConfigurableProduct to SimpleProduct link functionality
 */
class LinkManagementTest extends WebapiAbstract
{
    const SERVICE_NAME = 'configurableProductLinkManagementV1';
    const OPTION_SERVICE_NAME = 'configurableProductOptionRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/configurable-products';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * Execute per test initialization
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->attributeRepository = $this->objectManager->get(\Magento\Eav\Model\AttributeRepository::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @return void
     */
    public function testGetChildren(): void
    {
        $productSku = 'configurable';

        /** @var array $result */
        $result = $this->getChildren($productSku);
        $this->assertCount(2, $result);

        foreach ($result as $product) {
            $this->assertArrayHasKey('custom_attributes', $product);
            $this->assertArrayHasKey('price', $product);
            $this->assertArrayHasKey('updated_at', $product);

            $this->assertArrayHasKey('name', $product);
            $this->assertStringContainsString('Configurable Option', $product['name']);

            $this->assertArrayHasKey('sku', $product);
            $this->assertStringContainsString('simple_', $product['sku']);

            $this->assertArrayHasKey('status', $product);
            $this->assertEquals('1', $product['status']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_simple_77.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/delete_association.php
     *
     * @return void
     */
    public function testAddChild(): void
    {
        $productSku = 'configurable';
        $childSku = 'simple_77';
        $res = $this->addChild($productSku, $childSku);
        $this->assertTrue($res);
    }

    /**
     * Test the full flow of creating a configurable product and adding a child via REST
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     *
     * @return void
     */
    public function testAddChildFullRestCreation(): void
    {
        $productSku = 'configurable-product-sku';
        $childSku = 'simple-product-sku';

        $this->createConfigurableProduct($productSku);
        $attribute = $this->attributeRepository->get('catalog_product', 'test_configurable');

        $this->addOptionToConfigurableProduct(
            $productSku,
            (int)$attribute->getAttributeId(),
            [
                [
                    'value_index' => $attribute->getOptions()[1]->getValue()
                ]
            ]
        );

        $this->createSimpleProduct(
            $childSku,
            [
                [
                    'attribute_code' => 'test_configurable',
                    'value' => $attribute->getOptions()[1]->getValue()
                ]
            ]
        );

        $res = $this->addChild($productSku, $childSku);
        $this->assertTrue($res);

        // confirm that the simple product was added
        $children = $this->getChildren($productSku);
        $added = false;
        foreach ($children as $child) {
            if ($child['sku'] == $childSku) {
                $added = true;
                break;
            }
        }
        $this->assertTrue($added);

        // clean up products

        $this->deleteProduct($productSku);
        $this->deleteProduct($childSku);
    }

    /**
     * Test if configurable option attribute positions are being preserved after simple products were assigned to a
     * configurable product.
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_attributes_for_position_test.php
     *
     * @return void
     */
    public function testConfigurableOptionPositionPreservation(): void
    {
        $productSku = 'configurable-product-sku';
        $childProductSkus = [
            'simple-product-sku-1',
            'simple-product-sku-2'
        ];
        $attributesToAdd = [
            'custom_attr_1',
            'custom_attr_2',
        ];

        $this->createConfigurableProduct($productSku);

        $position = 0;
        $attributeOptions = [];
        foreach ($attributesToAdd as $attributeToAdd) {
            /** @var Attribute $attribute */
            $attribute = $this->attributeRepository->get('catalog_product', $attributeToAdd);

            /** @var Option $options [] */
            $options = $attribute->getOptions();
            array_shift($options);

            $attributeOptions[$attributeToAdd] = $options;

            $valueIndexesData = [];
            foreach ($options as $option) {
                $valueIndexesData[]['value_index'] = $option->getValue();
            }
            $this->addOptionToConfigurableProduct(
                $productSku,
                (int)$attribute->getAttributeId(),
                $valueIndexesData,
                $position
            );
            $position++;
        }

        $this->assertArrayHasKey($attributesToAdd[0], $attributeOptions);
        $this->assertArrayHasKey($attributesToAdd[1], $attributeOptions);
        $this->assertCount(4, $attributeOptions[$attributesToAdd[0]]);
        $this->assertCount(4, $attributeOptions[$attributesToAdd[1]]);

        $attributesBeforeAssign = $this->getConfigurableAttribute($productSku);

        $simpleProdsAttributeData = [];
        foreach ($attributeOptions as $attributeCode => $options) {
            $simpleProdsAttributeData [0][] = [
                'attribute_code' => $attributeCode,
                'value' => $options[0]->getValue(),
            ];
            $simpleProdsAttributeData [0][] = [
                'attribute_code' => $attributeCode,
                'value' => $options[1]->getValue(),
            ];
            $simpleProdsAttributeData [1][] = [
                'attribute_code' => $attributeCode,
                'value' => $options[2]->getValue(),
            ];
            $simpleProdsAttributeData [1][] = [
                'attribute_code' => $attributeCode,
                'value' => $options[3]->getValue(),
            ];
        }

        foreach ($childProductSkus as $childNum => $childSku) {
            $this->createSimpleProduct($childSku, $simpleProdsAttributeData[$childNum]);
            $res = $this->addChild($productSku, $childSku);
            $this->assertTrue($res);
        }

        $childProductsDiff = array_diff(
            $childProductSkus,
            array_column(
                $this->getChildren($productSku),
                'sku'
            )
        );
        $this->assertCount(0, $childProductsDiff, 'Added child product count mismatch expected result');

        $attributesAfterAssign = $this->getConfigurableAttribute($productSku);

        $this->assertEquals(
            $attributesBeforeAssign[0]['position'],
            $attributesAfterAssign[0]['position'],
            'Product 1 attribute option position mismatch'
        );
        $this->assertEquals(
            $attributesBeforeAssign[1]['position'],
            $attributesAfterAssign[1]['position'],
            'Product 2 attribute option position mismatch'
        );

        foreach ($childProductSkus as $childSku) {
            $this->deleteProduct($childSku);
        }
        $this->deleteProduct($productSku);
    }

    /**
     * Delete product by SKU
     *
     * @param string $sku
     * @return bool
     */
    private function deleteProduct(string $sku): bool
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => 'catalogProductRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogProductRepositoryV1DeleteById',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $sku]);
    }

    /**
     * Get configurable product attributes
     *
     * @param string $productSku
     * @return array
     */
    protected function getConfigurableAttribute(string $productSku): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/options/all',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::OPTION_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::OPTION_SERVICE_NAME . 'GetList'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku]);
    }

    /**
     * Perform add child product Api call
     *
     * @param string $productSku
     * @param string $childSku
     * @return array|int|string|float|bool
     */
    private function addChild(string $productSku, string $childSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/child',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'AddChild'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'childSku' => $childSku]);
    }

    /**
     * Perform create configurable product api call
     *
     * @param string $productSku
     * @return array|bool|float|int|string
     */
    protected function createConfigurableProduct(string $productSku)
    {
        $requestData = [
            'product' => [
                'sku' => $productSku,
                'name' => 'configurable-product-' . $productSku,
                'type_id' => 'configurable',
                'price' => 50,
                'attribute_set_id' => 4
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => 'catalogProductRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogProductRepositoryV1Save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Add option to configurable product
     *
     * @param string $productSku
     * @param int $attributeId
     * @param array $attributeValues
     * @param int $position
     * @return array|bool|float|int|string
     */
    protected function addOptionToConfigurableProduct(
        string $productSku,
        int $attributeId,
        array $attributeValues,
        int $position = 0
    ) {
        $requestData = [
            'sku' => $productSku,
            'option' => [
                'attribute_id' => $attributeId,
                'label' => 'test_configurable',
                'position' => $position,
                'is_use_default' => true,
                'values' => $attributeValues
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/configurable-products/' . $productSku . '/options',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'configurableProductOptionRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'configurableProductOptionRepositoryV1Save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    protected function createSimpleProduct($sku, $customAttributes)
    {
        $requestData = [
            'product' => [
                'sku' => $sku,
                'name' => 'simple-product-' . $sku,
                'type_id' => 'simple',
                'attribute_set_id' => 4,
                'price' => 3.62,
                'status' => 1,
                'visibility' => 4,
                'custom_attributes' => $customAttributes
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'catalogProductRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogProductRepositoryV1Save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @return void
     */
    public function testRemoveChild(): void
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';
        $this->assertTrue($this->removeChild($productSku, $childSku));
    }

    /**
     * @dataProvider errorsDataProvider
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Catalog/_files/second_product_simple.php
     *
     * @param string $parentSku
     * @param string $childSku
     * @param string $errorMessage
     * @return void
     */
    public function testAddChildWithError(string $parentSku, string $childSku, string $errorMessage): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($errorMessage);
        $this->addChild($parentSku, $childSku);
    }

    /**
     * @return array
     */
    public function errorsDataProvider(): array
    {
        return [
            'simple_instead_of_configurable' => [
                'parent_sku' => 'simple2',
                'child_sku' => 'configurable',
                'error_message' => (string)__("The parent product doesn't have configurable product options."),
            ],
            'simple_with_empty_configurable_attribute_value' => [
                'parent_sku' => 'configurable',
                'child_sku' => 'simple2',
                'error_message' => TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP
                    ? (string)__(
                        'The child product doesn\'t have the "%1" attribute value. Verify the value and try again.'
                    )
                    : (string)__(
                        'The child product doesn\'t have the \\"%1\\" attribute value. Verify the value and try again.'
                    ),
            ],
        ];
    }

    /**
     * Remove child product
     *
     * @param string $productSku
     * @param string $childSku
     * @return array|bool|float|int|string
     */
    protected function removeChild(string $productSku, string $childSku)
    {
        $resourcePath = self::RESOURCE_PATH . '/%s/children/%s';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf($resourcePath, $productSku, $childSku),
                'httpMethod' => Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'RemoveChild'
            ]
        ];
        $requestData = ['sku' => $productSku, 'childSku' => $childSku];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Get child products
     *
     * @param string $productSku
     * @return string[]
     */
    protected function getChildren(string $productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/children',
                'httpMethod' => Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetChildren'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku]);
    }
}
