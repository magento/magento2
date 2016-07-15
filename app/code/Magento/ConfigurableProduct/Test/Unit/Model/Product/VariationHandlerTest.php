<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\ConfigurableProduct\Model\Product\VariationHandler;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class VariationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VariationHandler
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\EntityFactory
     */
    protected $entityFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ProductFactory
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableProduct;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectHelper;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productFactoryMock = $this->getMock(
            'Magento\Catalog\Model\ProductFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->entityFactoryMock = $this->getMock(
            'Magento\Eav\Model\EntityFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->attributeSetFactory = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\SetFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->stockConfiguration = $this->getMock(
            'Magento\CatalogInventory\Api\StockConfigurationInterface',
            [],
            [],
            '',
            false
        );
        $this->configurableProduct = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [],
            [],
            '',
            false
        );

        $this->product = $this->getMock('Magento\Catalog\Model\Product', ['getMediaGallery'], [], '', false);

        $this->model = $this->objectHelper->getObject(
            'Magento\ConfigurableProduct\Model\Product\VariationHandler',
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

        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['getNewVariationsAttributeSetId'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock = $this->getMockBuilder('\Magento\Eav\Model\Entity\Attribute')
            ->setMethods(['isInSet', 'setAttributeSetId', 'setAttributeGroupId', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeSetMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Set')
            ->setMethods(['load', 'addSetInfo', 'getDefaultGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $eavEntityMock = $this->getMockBuilder('\Magento\Eav\Model\Entity')
            ->setMethods(['setType', 'getTypeId'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('getNewVariationsAttributeSetId')
            ->willReturn('new_attr_set_id');
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
        $attributeMock->expects($this->once())->method('isInSet')->with('new_attr_set_id')->willReturn(false);
        $attributeMock->expects($this->once())->method('setAttributeSetId')->with('new_attr_set_id')->willReturnSelf();
        $attributeSetMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->with('new_attr_set_id')
            ->willReturn('default_group_id');
        $attributeMock->expects($this->once())
            ->method('setAttributeGroupId')
            ->with('default_group_id')
            ->willReturnSelf();
        $attributeMock->expects($this->once())->method('save')->willReturnSelf();

        $this->model->prepareAttributeSet($productMock);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGenerateSimpleProducts()
    {
        $productsData = [
            6 =>
                [
                    'image' => 'image.jpg',
                    'name' => 'config-red',
                    'configurable_attribute' => '{"new_attr":"6"}',
                    'sku' => 'config-red',
                    'quantity_and_stock_status' =>
                        [
                            'qty' => '',
                        ],
                    'weight' => '333',
                ]
        ];
        $stockData = [
            'manage_stock' => '0',
            'use_config_enable_qty_increments' => '1',
            'use_config_qty_increments' => '1',
            'use_config_manage_stock' => 0,
            'is_decimal_divided' => 0
        ];

        $parentProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(
                [
                    '__wakeup',
                    'getNewVariationsAttributeSetId',
                    'getStockData',
                    'getQuantityAndStockStatus',
                    'getWebsiteIds'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $newSimpleProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(
                [
                    '__wakeup',
                    'save',
                    'getId',
                    'setStoreId',
                    'setTypeId',
                    'setAttributeSetId',
                    'getTypeInstance',
                    'getStoreId',
                    'addData',
                    'setWebsiteIds',
                    'setStatus',
                    'setVisibility'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Type')
            ->setMethods(['getSetAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $editableAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->setMethods(['getIsUnique', 'getAttributeCode', 'getFrontend', 'getIsVisible'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Frontend')
            ->setMethods(['getInputType'])
            ->disableOriginalConstructor()
            ->getMock();

        $parentProductMock->expects($this->once())
            ->method('getNewVariationsAttributeSetId')
            ->willReturn('new_attr_set_id');
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($newSimpleProductMock);
        $newSimpleProductMock->expects($this->once())->method('setStoreId')->with(0)->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('setTypeId')->with('simple')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with('new_attr_set_id')
            ->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('getTypeInstance')->willReturn($productTypeMock);
        $productTypeMock->expects($this->once())
            ->method('getSetAttributes')
            ->with($newSimpleProductMock)
            ->willReturn([$editableAttributeMock]);
        $editableAttributeMock->expects($this->once())->method('getIsUnique')->willReturn(false);
        $editableAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn('some_code');
        $editableAttributeMock->expects($this->any())->method('getFrontend')->willReturn($frontendAttributeMock);
        $frontendAttributeMock->expects($this->any())->method('getInputType')->willReturn('input_type');
        $editableAttributeMock->expects($this->any())->method('getIsVisible')->willReturn(false);
        $parentProductMock->expects($this->once())->method('getStockData')->willReturn($stockData);
        $parentProductMock->expects($this->once())
            ->method('getQuantityAndStockStatus')
            ->willReturn(['is_in_stock' => 1]);
        $newSimpleProductMock->expects($this->once())->method('addData')->willReturnSelf();
        $parentProductMock->expects($this->once())->method('getWebsiteIds')->willReturn('website_id');
        $newSimpleProductMock->expects($this->once())->method('setWebsiteIds')->with('website_id')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('setVisibility')->with(1)->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('save')->willReturnSelf();
        $newSimpleProductMock->expects($this->once())->method('getId')->willReturn('product_id');

        $this->assertEquals(['product_id'], $this->model->generateSimpleProducts($parentProductMock, $productsData));
    }

    public function testProcessMediaGalleryWithImagesAndGallery()
    {
        $this->product->expects($this->atLeastOnce())->method('getMediaGallery')->with('images')->willReturn([]);
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
        $this->product->expects($this->atLeastOnce())->method('getMediaGallery')->with('images')->willReturn([]);
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
        $this->product->expects($this->once())->method('getMediaGallery')->with('images')->willReturn([]);
        $productData['image'] = false;
        $productData['media_gallery']['images'] = false;
        $result = $this->model->processMediaGallery($this->product, $productData);
        $this->assertEquals($productData, $result);
    }

    /**
     * @dataProvider productDataProviderForProcessMediaGalleryForFillingGallery
     * @param array $productData
     * @param array $expected
     */
    public function testProcessMediaGalleryForFillingGallery($productData, $expected)
    {
        $this->assertEquals($expected, $this->model->processMediaGallery($this->product, $productData));
    }

    /**
     * @return array
     */
    public function productDataProviderForProcessMediaGalleryForFillingGallery()
    {
        return [
            'empty array' => [
                [], [],
            ],
            'array only with empty image' => [
                'given' => [
                    'image',
                ],
                'expected' => [
                    'image',
                ],
            ],
            'empty array with not empty image' => [
                'given' => [
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
