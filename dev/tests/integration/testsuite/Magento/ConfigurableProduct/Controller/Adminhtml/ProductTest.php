<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\Product\Type\Virtual;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for configurable product admin save.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ProductTest extends AbstractBackendController
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->productAttributeRepository = $this->_objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->registry = $this->_objectManager->get(Registry::class);
        $this->jsonSerializer = $this->_objectManager->get(SerializerInterface::class);
        $this->eavConfig = $this->_objectManager->get(Config::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/associated_products.php
     * @return void
     */
    public function testSaveActionAssociatedProductIds(): void
    {
        $associatedProductIds = ['3', '14', '15', '92'];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'id' => 1,
                'attributes' => [$this->getAttribute('test_configurable')->getId()],
                'associated_product_ids_serialized' => $this->jsonSerializer->serialize($associatedProductIds),
            ]
        );
        $this->dispatch('backend/catalog/product/save');
        $this->assertSessionMessages($this->equalTo([__('You saved the product.')]), MessageInterface::TYPE_SUCCESS);
        $this->assertRegistryConfigurableLinks($associatedProductIds);
        $this->assertConfigurableLinks('configurable', $associatedProductIds);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @dataProvider saveNewProductDataProvider
     * @param array $childProducts
     * @return void
     */
    public function testSaveNewProduct(array $childProducts): void
    {
        $this->serRequestParams($childProducts);
        $this->dispatch('backend/catalog/product/save');
        $this->assertSessionMessages($this->equalTo([__('You saved the product.')]), MessageInterface::TYPE_SUCCESS);
        $this->assertChildProducts($childProducts);
        $this->assertConfigurableOptions('configurable', $childProducts);
        $this->assertConfigurableLinks('configurable', $this->getProductIds(array_keys($childProducts)));
    }

    /**
     * @return array
     */
    public static function saveNewProductDataProvider(): array
    {
        return [
            'with_different_prices_and_qty' => [
                'childProducts' => [
                    'simple_1' => [
                        'name' => 'simple_1',
                        'sku' => 'simple_1',
                        'price' => 200,
                        'weight' => '1',
                        'qty' => '100',
                        'attributes' => ['test_configurable' => 'Option 1'],
                    ],
                    'simple_2' => [
                        'name' => 'simple_2',
                        'sku' => 'simple_2',
                        'price' => 100,
                        'weight' => '1',
                        'qty' => '200',
                        'attributes' => ['test_configurable' => 'Option 2'],
                    ],
                ],
            ],
            'without_weight' => [
                'childProducts' => [
                    'simple_1' => [
                        'name' => 'simple_1',
                        'sku' => 'simple_1',
                        'price' => 100,
                        'qty' => '100',
                        'attributes' => ['test_configurable' => 'Option 1'],
                    ],
                    'simple_2' => [
                        'name' => 'simple_2',
                        'sku' => 'simple_2',
                        'price' => 100,
                        'qty' => '100',
                        'attributes' => ['test_configurable' => 'Option 2'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_one_simple.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute_2.php
     * @dataProvider saveExistProductDataProvider
     * @param array $childProducts
     * @param array $associatedProducts
     * @return void
     */
    public function testSaveExistProduct(array $childProducts, array $associatedProducts): void
    {
        $configurableProduct = $this->productRepository->get('configurable');
        $this->serRequestParams($childProducts, $associatedProducts, (int)$configurableProduct->getId());
        $this->dispatch('backend/catalog/product/save');
        $this->assertSessionMessages($this->equalTo([__('You saved the product.')]), MessageInterface::TYPE_SUCCESS);
        $this->assertChildProducts($childProducts);
        $this->assertConfigurableOptions('configurable', $childProducts);
        $this->assertConfigurableLinks(
            'configurable',
            $this->getProductIds(array_merge($associatedProducts, array_keys($childProducts)))
        );
    }

    /**
     * @return array
     */
    public static function saveExistProductDataProvider(): array
    {
        return [
            'added_new_option' => [
                'childProducts' => [
                    'simple_2' => [
                        'name' => 'simple_2',
                        'sku' => 'simple_2',
                        'price' => 100,
                        'weight' => '1',
                        'qty' => '200',
                        'attributes' => ['test_configurable' => 'Option 2'],
                    ],
                ],
                'associatedProducts' => ['simple_1'],
            ],
            'added_new_option_and_delete_old' => [
                'childProducts' => [
                    'simple_2' => [
                        'name' => 'simple_2',
                        'sku' => 'simple_2',
                        'price' => 100,
                        'qty' => '100',
                        'attributes' => ['test_configurable' => 'Option 2'],
                    ],
                ],
                'associatedProducts' => [],
            ],
            'delete_all_options' => [
                'childProducts' => [],
                'associatedProducts' => [],
            ],
            'added_new_attribute' => [
                'childProducts' => [
                    'simple_1_1' => [
                        'name' => 'simple_1_1',
                        'sku' => 'simple_1_1',
                        'price' => 100,
                        'weight' => '1',
                        'qty' => '200',
                        'attributes' => [
                            'test_configurable' => 'Option 1',
                            'test_configurable_2' => 'Option 1',
                        ],
                    ],
                    'simple_1_2' => [
                        'name' => 'simple_1_2',
                        'sku' => 'simple_1_2',
                        'price' => 100,
                        'weight' => '1',
                        'qty' => '200',
                        'attributes' => [
                            'test_configurable' => 'Option 1',
                            'test_configurable_2' => 'Option 2',
                        ],
                    ],
                ],
                'associatedProducts' => [],
            ],
            'added_new_attribute_and_delete_old' => [
                'childProducts' => [
                    'simple_2_1' => [
                        'name' => 'simple_2_1',
                        'sku' => 'simple_2_1',
                        'price' => 100,
                        'qty' => '100',
                        'attributes' => ['test_configurable_2' => 'Option 1'],
                    ],
                    'simple_2_2' => [
                        'name' => 'simple_2_2',
                        'sku' => 'simple_2_2',
                        'price' => 100,
                        'qty' => '100',
                        'attributes' => ['test_configurable_2' => 'Option 2'],
                    ],
                ],
                'associatedProducts' => [],
            ],
        ];
    }

    /**
     * Sets products data into request.
     *
     * @param array $childProducts
     * @param array|null $associatedProducts
     * @param int|null $mainProductId
     * @return void
     */
    private function serRequestParams(
        array $childProducts,
        ?array $associatedProducts = [],
        ?int $mainProductId = null
    ): void {
        $this->setVariationMatrix($childProducts);
        $this->setAssociatedProducts($associatedProducts);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams(
            [
                'type' => Configurable::TYPE_CODE,
                'set' => $this->getDefaultAttributeSetId(),
                'id' => $mainProductId,
            ]
        );
        $this->getRequest()->setPostValue(
            'product',
            [
                'attribute_set_id' => $this->getDefaultAttributeSetId(),
                'name' => 'configurable',
                'sku' => 'configurable',
                'configurable_attributes_data' => $this->getConfigurableAttributesData($childProducts) ?: null,
            ]
        );
    }

    /**
     * Asserts product configurable links.
     *
     * @param string $sku
     * @param array $associatedProductIds
     * @return void
     */
    private function assertConfigurableLinks(string $sku, array $associatedProductIds): void
    {
        $product = $this->productRepository->get($sku, false, null, true);
        $this->assertEquals(
            $associatedProductIds,
            array_values($product->getExtensionAttributes()->getConfigurableProductLinks() ?: []),
            'Product links are not available in the database'
        );
    }

    /**
     * Asserts product from registry configurable links.
     *
     * @param array $associatedProductIds
     * @return void
     */
    private function assertRegistryConfigurableLinks(array $associatedProductIds): void
    {
        $product = $this->registry->registry('current_product');
        $this->assertNotNull($product);
        $this->assertEquals(
            $associatedProductIds,
            array_values($product->getExtensionAttributes()->getConfigurableProductLinks() ?: []),
            'Product links are not available in the registry'
        );
    }

    /**
     * Asserts child products data.
     *
     * @param array $childProducts
     * @return void
     */
    private function assertChildProducts(array $childProducts): void
    {
        foreach ($this->getProducts(array_column($childProducts, 'sku')) as $product) {
            $expectedProduct = $childProducts[$product->getSku()];
            $this->assertEquals($expectedProduct['price'], $product->getPrice());

            if (!empty($expectedProduct['weight'])) {
                $this->assertEquals($expectedProduct['weight'], (double)$product->getWeight());
                $this->assertInstanceOf(Simple::class, $product->getTypeInstance());
            } else {
                $this->assertInstanceOf(Virtual::class, $product->getTypeInstance());
            }

            $this->assertEquals($expectedProduct['qty'], $product->getExtensionAttributes()->getStockItem()->getQty());
        }
    }

    /**
     * Asserts that configurable attributes present in product configurable option list.
     *
     * @param string $sku
     * @param array $childProducts
     * @return void
     */
    private function assertConfigurableOptions(string $sku, array $childProducts): void
    {
        $configurableProduct = $this->productRepository->get($sku, false, null, true);
        $options = $configurableProduct->getExtensionAttributes()->getConfigurableProductOptions();
        if (empty($childProducts)) {
            $this->assertNull($options);
        } else {
            foreach ($options as $option) {
                $attribute = $this->getAttribute($option->getAttributeId());
                foreach ($childProducts as $childProduct) {
                    $this->assertContains($attribute->getAttributeCode(), array_keys($childProduct['attributes']));
                }
            }
        }
    }

    /**
     * Sets configurable product params to request.
     *
     * @param array $childProducts
     * @return void
     */
    private function setVariationMatrix(array $childProducts): void
    {
        $matrix = $attributeIds = $configurableAttributes = [];
        foreach ($childProducts as $product) {
            foreach ($product['attributes'] as $attributeCode => $optionLabel) {
                $attribute = $this->getAttribute($attributeCode);
                $configurableAttributes[$attributeCode] = $attribute->getSource()->getOptionId($optionLabel);
                $attributeIds[] = $attribute->getAttributeId();
            }
            $product['status'] = Status::STATUS_ENABLED;
            $product['configurable_attribute'] = $this->jsonSerializer->serialize($configurableAttributes);
            $product['newProduct'] = 1;
            $product['variationKey'] = implode('-', array_values($configurableAttributes));
            $matrix[] = $product;
        }
        $this->getRequest()->setPostValue(
            [
                'affect_configurable_product_attributes' => 1,
                'attributes' => $attributeIds,
                'new-variations-attribute-set-id' => $this->getDefaultAttributeSetId(),
                'configurable-matrix-serialized' => $this->jsonSerializer->serialize($matrix),
            ]
        );
    }

    /**
     * Sets associated product ids param to request.
     *
     * @param array|null $associatedProducts
     */
    private function setAssociatedProducts(?array $associatedProducts): void
    {
        if (!empty($associatedProducts)) {
            $associatedProductIds = array_map(
                function (ProductInterface $product) {
                    return $product->getId();
                },
                $this->getProducts($associatedProducts)
            );
            $this->getRequest()->setPostValue(
                'associated_product_ids_serialized',
                $this->jsonSerializer->serialize($associatedProductIds)
            );
        }
    }

    /**
     * Returns product configurable attributes data.
     *
     * @param array $childProducts
     * @return array
     */
    private function getConfigurableAttributesData(array $childProducts): array
    {
        $result = [];
        foreach ($childProducts as $product) {
            foreach ($product['attributes'] as $attributeCode => $optionLabel) {
                $attribute = $this->getAttribute($attributeCode);
                $optionId = $attribute->getSource()->getOptionId($optionLabel);
                if (empty($result[$attribute->getAttributeId()])) {
                    $result[$attribute->getAttributeId()] = [
                        'attribute_id' =>$attribute->getAttributeId(),
                        'code' => $attribute->getAttributeCode(),
                        'label' => $attribute->getAttributeCode(),
                        'position' => '0',
                        'values' => [
                            $optionId => [
                                'include' => '1',
                                'value_index' => $optionId,
                            ],
                        ],
                    ];
                } else {
                    $result[$attribute->getAttributeId()]['values'][$optionId] = [
                        'include' => '1',
                        'value_index' => $optionId,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve default product attribute set id.
     *
     * @return int
     */
    private function getDefaultAttributeSetId(): int
    {
        return (int)$this->eavConfig
            ->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getDefaultAttributeSetId();
    }

    /**
     * Retrieve configurable attribute instance.
     *
     * @param string $attributeCode
     * @return ProductAttributeInterface
     */
    private function getAttribute(string $attributeCode): ProductAttributeInterface
    {
        return $this->productAttributeRepository->get($attributeCode);
    }

    /**
     * Returns products by sku list.
     *
     * @param array $skuList
     * @return ProductInterface[]
     */
    private function getProducts(array $skuList): array
    {
        $result = [];
        foreach ($skuList as $sku) {
            $result[] = $this->productRepository->get($sku);
        }

        return $result;
    }

    /**
     * Returns product ids by sku list.
     *
     * @param array $skuList
     * @return array
     */
    private function getProductIds(array $skuList): array
    {
        $associatedProductIds = [];
        foreach ($this->getProducts($skuList) as $product) {
            $associatedProductIds[] = $product->getId();
        }

        return $associatedProductIds;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
