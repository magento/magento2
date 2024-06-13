<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;
use Magento\Eav\Model\Entity;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\FrontendInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VariationHandlerTest extends TestCase
{
    /**
     * @var VariationHandler
     */
    protected $model;

    /**
     * @var MockObject|SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var MockObject|EntityFactory
     */
    protected $entityFactoryMock;

    /**
     * @var MockObject|ProductFactory
     */
    protected $productFactoryMock;

    /**
     * @var MockObject|StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var MockObject|Configurable
     */
    protected $configurableProduct;

    /**
     * @var ObjectManager
     */
    protected $objectHelper;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectHelper = new ObjectManager($this);
        $this->productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->entityFactoryMock = $this->createPartialMock(EntityFactory::class, ['create']);
        $this->attributeSetFactory = $this->createPartialMock(
            SetFactory::class,
            ['create']
        );
        $this->stockConfiguration = $this->createMock(StockConfigurationInterface::class);
        $this->configurableProduct = $this->createMock(
            Configurable::class
        );

        $this->product = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();

        $this->model = $this->objectHelper->getObject(
            VariationHandler::class,
            [
                'productFactory' => $this->productFactoryMock,
                'entityFactory' => $this->entityFactoryMock,
                'attributeSetFactory' => $this->attributeSetFactory,
                'stockConfiguration' => $this->stockConfiguration,
                'configurableProduct' => $this->configurableProduct
            ]
        );
    }

    public function testPrepareAttributeSet()
    {
        $productMock = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        $attributeMock = new \Magento\Eav\Test\Unit\Helper\AttributeTestHelper();
        $attributeSetMock = $this->createPartialMock(Set::class, ['load', 'addSetInfo', 'getDefaultGroupId']);
        $eavEntityMock = $this->createPartialMock(Entity::class, ['setType', 'getTypeId']);

        // $productMock anonymous class returns 'new_attr_set_id' for getNewVariationsAttributeSetId()
        $this->configurableProduct->expects($this->once())
            ->method('getUsedProductAttributes')
            ->with($productMock)
            ->willReturn([$attributeMock]);
        $this->attributeSetFactory->expects($this->once())->method('create')->willReturn($attributeSetMock);
        $attributeSetMock->expects($this->once())->method('load')->with('new_attr_set_id')->willReturnSelf();
        $this->entityFactoryMock->expects($this->once())->method('create')->willReturn($eavEntityMock);
        $eavEntityMock->expects($this->once())->method('setType')->with('catalog_product')->willReturnSelf();
        $eavEntityMock->expects($this->once())->method('getTypeId')->willReturn('type_id');
        $attributeSetMock->expects($this->once())->method('addSetInfo')->with('type_id', [$attributeMock]);
        // Configure AttributeTestHelper with expected values
        $attributeSetMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->with('new_attr_set_id')
            ->willReturn('default_group_id');

        $this->model->prepareAttributeSet($productMock);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param int|string|null $weight
     * @param string $typeId
     */
    #[DataProvider('dataProviderTestGenerateSimpleProducts')]
    public function testGenerateSimpleProducts($weight, $typeId)
    {
        $productsData = [
            [
                'image' => 'image.jpg',
                'name' => 'config-red',
                'configurable_attribute' => '{"new_attr":"6"}',
                'sku' => 'config-red',
                'quantity_and_stock_status' => [
                    'qty' => '',
                ],
            ]
        ];

        // Do not add 'weight' attribute if it's value is null!
        if ($weight !== null) {
            $productsData[0]['weight'] = $weight;
        }

        $stockData = [
            'manage_stock' => '0',
            'use_config_enable_qty_increments' => '1',
            'use_config_qty_increments' => '1',
            'use_config_manage_stock' => 0,
            'is_decimal_divided' => 0
        ];

        $parentProductMock = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        $newSimpleProductMock = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        $editableAttributeMock = new \Magento\Eav\Test\Unit\Helper\AttributeTestHelper();

        // Create mock for frontend attribute
        $frontendAttributeMock = $this->createMock(DefaultFrontend::class);
        $frontendAttributeMock->method('getInputType')->willReturn('input_type');

        // Helper classes provide default return values
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($newSimpleProductMock);
        // ProductTestHelper methods return $this by default for fluent interface
        // Anonymous class returns empty array by default for getSetAttributes
        // Configure AttributeTestHelper with expected values
        $editableAttributeMock->setData('frontend', $frontendAttributeMock);
        // Frontend mock returns 'input_type' for getInputType()
        // Configure helper objects with expected values
        $parentProductMock->setData('stock_data', $stockData);
        $parentProductMock->setData('quantity_and_stock_status', ['is_in_stock' => 1]);
        $parentProductMock->setData('website_ids', 'website_id');
        // ProductTestHelper methods return $this by default, configure expected ID
        $newSimpleProductMock->setId('product_id');

        $this->assertEquals(['product_id'], $this->model->generateSimpleProducts($parentProductMock, $productsData));
    }

    /**
     * @return array
     */
    public static function dataProviderTestGenerateSimpleProducts()
    {
        return [
            [
                'weight' => 333,
                'typeId' => Type::TYPE_SIMPLE,
            ],
            [
                'weight' => '',
                'typeId' => Type::TYPE_VIRTUAL,
            ],
            [
                'weight' => null,
                'typeId' => Type::TYPE_VIRTUAL,
            ],
        ];
    }

    public function testProcessMediaGalleryWithImagesAndGallery()
    {
        // $this->product anonymous class returns [] for getMediaGallery()
        $productData['image'] = 'test';
        $productData['media_gallery']['images'] = [
            [
                'file' => 'test',
            ],
        ];
        $result = $this->model->processMediaGallery($this->product, $productData);
        $this->assertEquals($productData, $result);
    }

    public function testProcessMediaGalleryIfImageIsEmptyButProductMediaGalleryIsNotEmpty()
    {
        // $this->product anonymous class returns [] for getMediaGallery()
        $productData['image'] = false;
        $productData['media_gallery']['images'] = [
            [
                'name' => 'test',
            ],
        ];
        $result = $this->model->processMediaGallery($this->product, $productData);
        $this->assertEquals($productData, $result);
    }

    public function testProcessMediaGalleryIfProductDataHasNoImagesAndGallery()
    {
        // $this->product anonymous class returns [] for getMediaGallery()
        $productData['image'] = false;
        $productData['media_gallery']['images'] = false;
        $result = $this->model->processMediaGallery($this->product, $productData);
        $this->assertEquals($productData, $result);
    }

    /**
     * @param array $productData
     * @param array $expected
     */
    #[DataProvider('productDataProviderForProcessMediaGalleryForFillingGallery')]
    public function testProcessMediaGalleryForFillingGallery($productData, $expected)
    {
        $this->assertEquals($expected, $this->model->processMediaGallery($this->product, $productData));
    }

    /**
     * @return array
     */
    public static function productDataProviderForProcessMediaGalleryForFillingGallery()
    {
        return [
            'empty array' => [
                [], [],
            ],
            'array only with empty image' => [
                'productData' => [
                    'image',
                ],
                'expected' => [
                    'image',
                ],
            ],
            'empty array with not empty image' => [
                'productData' => [
                    'image' => 1,
                ],
                'expected' => [
                    'thumbnail' => 1,
                    'media_gallery' => [
                        'images' => [
                            0 => [
                                'position' => 1,
                                'file' => '1',
                                'disabled' => 0,
                                'label' => '',
                            ],
                        ],
                    ],
                    'image' => 1,
                    'small_image' => 1,
                ],
            ],
        ];
    }
}
