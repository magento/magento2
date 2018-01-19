<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

use Magento\Eav\Model\AttributeRepository;

class LinkManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'configurableProductLinkManagementV1';
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
    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->attributeRepository = $this->objectManager->get(\Magento\Eav\Model\AttributeRepository::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetChildren()
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
            $this->assertContains('Configurable Option', $product['name']);

            $this->assertArrayHasKey('sku', $product);
            $this->assertContains('simple_', $product['sku']);

            $this->assertArrayHasKey('status', $product);
            $this->assertEquals('1', $product['status']);

            $this->assertArrayHasKey('visibility', $product);
            $this->assertEquals('1', $product['visibility']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_simple_77.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/delete_association.php
     */
    public function testAddChild()
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
     */
    public function testAddChildFullRestCreation()
    {
        $productSku = 'configurable-product-sku';
        $childSku = 'simple-product-sku';

        $this->createConfigurableProduct($productSku);
        $attribute = $this->attributeRepository->get('catalog_product', 'test_configurable');
        $attributeValue = $attribute->getOptions()[1]->getValue();
        $this->addOptionToConfigurableProduct($productSku, $attribute->getAttributeId(), $attributeValue);
        $this->createSimpleProduct($childSku, $attributeValue);
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
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $productSku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => 'catalogProductRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogProductRepositoryV1DeleteById',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['sku' => $productSku]);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $childSku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => 'catalogProductRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogProductRepositoryV1DeleteById',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['sku' => $childSku]);
    }

    private function addChild($productSku, $childSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/child',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'AddChild'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'childSku' => $childSku]);
    }

    protected function createConfigurableProduct($productSku)
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => 'catalogProductRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogProductRepositoryV1Save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    protected function addOptionToConfigurableProduct($productSku, $attributeId, $attributeValue)
    {
        $requestData = [
            'sku' => $productSku,
            'option' => [
                'attribute_id' => $attributeId,
                'label' => 'test_configurable',
                'position' => 0,
                'is_use_default' => true,
                'values' => [
                    ['value_index' => $attributeValue],
                ]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/configurable-products/'. $productSku .'/options',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'configurableProductOptionRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'configurableProductOptionRepositoryV1Save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    protected function createSimpleProduct($sku, $attributeValue)
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
                'custom_attributes' => [
                    ['attribute_code' => 'test_configurable', 'value' => $attributeValue],
                ]
            ]
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
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
     */
    public function testRemoveChild()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';
        $this->assertTrue($this->removeChild($productSku, $childSku));
    }

    protected function removeChild($productSku, $childSku)
    {
        $resourcePath = self::RESOURCE_PATH . '/%s/children/%s';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf($resourcePath, $productSku, $childSku),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE
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
     * @param string $productSku
     * @return string[]
     */
    protected function getChildren($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku  . '/children',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
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
