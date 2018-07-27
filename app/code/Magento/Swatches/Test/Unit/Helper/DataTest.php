<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Helper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Helper\Image */
    protected $imageHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $productCollectionFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Product\Collection */
    protected $productCollectionMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ConfigurableProduct\Model\Product\Type\Configurable */
    protected $configurableMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ProductFactory */
    protected $productModelFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product */
    protected $productMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManager */
    protected $storeManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory */
    protected $swatchCollectionFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\Eav\Attribute */
    protected $attributeMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager|\Magento\Swatches\Helper\Data */
    protected $swatchHelperObject;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepoMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Swatches\Model\SwatchAttributesProvider */
    private $swatchAttributesProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Unserialize\SecureUnserializer
     */
    protected $secureUnserializerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->imageHelperMock = $this->getMock(\Magento\Catalog\Helper\Image::class, [], [], '', false);
        $this->productCollectionFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        $this->productCollectionMock = $this->objectManager->getCollectionMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [
                $this->productMock,
                $this->productMock,
            ]
        );

        $this->configurableMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            [],
            [],
            '',
            false
        );
        $this->productModelFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->productRepoMock = $this->getMock(
            \Magento\Catalog\Api\ProductRepositoryInterface::class,
            [],
            [],
            '',
            false
        );

        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $this->swatchCollectionFactoryMock = $this->getMock(
            \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->attributeMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [],
            [],
            '',
            false
        );

        $this->swatchAttributesProviderMock = $this->getMock(
            \Magento\Swatches\Model\SwatchAttributesProvider::class,
            ['provide'],
            [],
            '',
            false
        );

        $this->secureUnserializerMock = $this->getMock(
            \Magento\Framework\Unserialize\SecureUnserializer::class,
            ['unserializer'],
            [],
            '',
            false
        );

        $this->swatchHelperObject = $this->objectManager->getObject(
            \Magento\Swatches\Helper\Data::class,
            [
                'productCollectionFactory' => $this->productCollectionFactoryMock,
                'configurable' => $this->configurableMock,
                'productRepository' => $this->productRepoMock,
                'storeManager' => $this->storeManagerMock,
                'swatchCollectionFactory' => $this->swatchCollectionFactoryMock,
                'imageHelper' => $this->imageHelperMock,
                'swatchAttributesProvider' => $this->swatchAttributesProviderMock,
                'unserializer' => $this->secureUnserializerMock,
            ]
        );
    }

    /**
     * @return array
     */
    public function dataForAdditionalData()
    {
        $additionalData = [
            'swatch_input_type' => 'visual',
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];

        return [
            [
                serialize($additionalData),
                [
                    'getData' => 1,
                    'setData' => 3,
                ]
            ],
            [
                null,
                [
                    'getData' => 1,
                    'setData' => 0,
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataForAssembleEavAttribute
     */
    public function testAssembleAdditionalDataEavAttribute($dataFromDb, $attributeData)
    {
        $this->attributeMock
            ->expects($this->at(0))
            ->method('getData')
            ->with('additional_data')
            ->will($this->returnValue($dataFromDb));

        $i = 1;
        foreach ($attributeData as $key => $value) {
            $this->attributeMock
                ->expects($this->at($i))
                ->method('getData')
                ->with($key)
                ->willReturn($value);
            $i++;
        }

        $this->attributeMock->expects($this->once())->method('setData');

        $this->swatchHelperObject->assembleAdditionalDataEavAttribute($this->attributeMock);
    }

    /**
     * @return array
     */
    public function dataForAssembleEavAttribute()
    {
        $additionalData = [
            'swatch_input_type' => 'visual',
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];

        return [
            [
                serialize($additionalData),
                [
                    'swatch_input_type' => 'visual',
                    'update_product_preview_image' => 1,
                    'use_product_image_for_swatch' => 1,
                ],
            ],
            [
                null,
                [
                    'swatch_input_type' => null,
                    'update_product_preview_image' => 0,
                    'use_product_image_for_swatch' => 0,
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForVariationWithSwatchImage
     */
    public function testLoadFirstVariationWithSwatchImage($imageTypes, $expected, $requiredAttributes)
    {
        $this->getSwatchAttributes($this->productMock);
        $this->getUsedProducts($imageTypes + $requiredAttributes, $imageTypes);

        $result = $this->swatchHelperObject->loadFirstVariationWithSwatchImage($this->productMock, $requiredAttributes);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $result);
        }
    }

    /**
     * @return array
     */
    public function dataForVariationWithSwatchImage()
    {
        return [
            [
                [
                    'image' => '/m/a/magento.png',
                    'small_image' => '/m/a/magento.png',
                    'thumbnail' => '/m/a/magento.png',
                    'swatch_image' => '/m/a/magento.png', //important
                ],
                \Magento\Catalog\Model\Product::class,
                ['color' => 31]
            ],
            [
                [
                    'image' => '/m/a/magento.png',
                    'small_image' => '/m/a/magento.png',
                    'thumbnail' => '/m/a/magento.png',
                ],
                false,
                ['size' => 31]
            ],
        ];
    }

    /**
     * @dataProvider dataForCreateSwatchProductByFallback
     */
    public function testLoadVariationByFallback($product)
    {
        $this->getSwatchAttributes($product);

        $this->prepareVariationCollection();

        $this->productCollectionMock->method('getFirstItem')->willReturn($this->productMock);
        $this->productMock->method('getId')->willReturn(95);
        $this->productModelFactoryMock->method('create')->willReturn($this->productMock);
        $this->productMock->method('load')->with(95)->will($this->returnSelf());

        $this->swatchHelperObject->loadVariationByFallback($this->productMock, ['color' => 31]);
    }

    /**
     * @dataProvider dataForVariationWithImage
     */
    public function testLoadFirstVariationWithImage($imageTypes, $expected, $requiredAttributes)
    {
        $this->getSwatchAttributes($this->productMock);
        $this->getUsedProducts($imageTypes + $requiredAttributes, $imageTypes);

        $result = $this->swatchHelperObject->loadFirstVariationWithImage($this->productMock, $requiredAttributes);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $result);
        }
    }

    /**
     * @return array
     */
    public function dataForVariationWithImage()
    {
        return [
            [
                [
                    'image' => '/m/a/magento.png', //important
                    'small_image' => '/m/a/magento.png',
                    'thumbnail' => '/m/a/magento.png',
                    'swatch_image' => '/m/a/magento.png',
                ],
                \Magento\Catalog\Model\Product::class,
                ['color' => 31]
            ],
            [
                [
                    'small_image' => '/m/a/magento.png',
                    'thumbnail' => '/m/a/magento.png',
                    'swatch_image' => '/m/a/magento.png',
                ],
                false,
                ['size' => 31]
            ],
        ];
    }

    public function testLoadFirstVariationWithImageNoProduct()
    {
        $result = $this->swatchHelperObject->loadVariationByFallback($this->productMock, ['color' => 31]);
        $this->assertFalse($result);
    }

    public function testLoadVariationByFallbackWithoutProduct()
    {
        $result = $this->swatchHelperObject->loadFirstVariationWithImage($this->productMock, ['color' => 31]);
        $this->assertFalse($result);
    }

    /**
     * @dataProvider dataForMediaGallery
     */
    public function testGetProductMediaGallery($mediaGallery, $image)
    {
        $mediaGalleryEntries = [];
        $id = 0;
        $mediaUrls = [];
        foreach ($mediaGallery as $mediaType => $mediaFile) {
            $mediaGalleryEntryMock = $this->getMockBuilder(
                \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
            )->getMock();
            $mediaGalleryEntryMock->expects($this->atLeastOnce())
                ->method('isDisabled')
                ->willReturn(false);
            $mediaGalleryEntryMock->expects($this->atLeastOnce())
                ->method('getTypes')
                ->willReturn([$mediaType]);
            $mediaGalleryEntryMock->expects($this->atLeastOnce())
                ->method('getFile')
                ->willReturn($mediaFile);
            $mediaGalleryEntryMock->expects($this->atLeastOnce())
                ->method('getId')
                ->willReturn(++$id);

            $mediaGalleryEntries[] = $mediaGalleryEntryMock;
            $mediaUrls[] = ['http://full_path_to_image' . $mediaFile]; //large
            $mediaUrls[] = ['http://full_path_to_image' . $mediaFile]; //medium
            $mediaUrls[] = ['http://full_path_to_image' . $mediaFile]; //small
        }
        $this->productMock->expects($this->once())
            ->method('getMediaGalleryEntries')
            ->willReturn($mediaGalleryEntries);

        if ($mediaGallery) {
            $this->imageHelperMock->expects($this->atLeastOnce())
                ->method('init')
                ->willReturnMap([
                    [$this->productMock, 'product_page_image_large', [], $this->imageHelperMock],
                    [$this->productMock, 'product_page_image_medium', [], $this->imageHelperMock],
                    [$this->productMock, 'product_page_image_small', [], $this->imageHelperMock],
                ]);
            $this->imageHelperMock->expects($this->atLeastOnce())
                ->method('setImageFile')
                ->willReturnSelf();
            $this->imageHelperMock->expects($this->atLeastOnce())
                ->method('constrainOnly')
                ->willReturnSelf();
            $this->imageHelperMock->expects($this->atLeastOnce())
                ->method('keepAspectRatio')
                ->willReturnSelf();
            $this->imageHelperMock->expects($this->atLeastOnce())
                ->method('keepFrame')
                ->willReturnSelf();
            $this->imageHelperMock->expects($this->atLeastOnce())
                ->method('getUrl')
                ->willReturnMap($mediaUrls);
        }

        $productMediaGallery = $this->swatchHelperObject->getProductMediaGallery($this->productMock);
        if ($mediaGallery) {
            $this->assertContains($image, $productMediaGallery['large']);
            $this->assertContains($image, $productMediaGallery['medium']);
            $this->assertContains($image, $productMediaGallery['small']);
        } else {
            $this->assertEmpty($productMediaGallery);
        }
    }

    /**
     * @return array
     */
    public function dataForMediaGallery()
    {
        return [
            [
                [
                    'image' => '/m/a/magento1.png',
                    'small_image' => '/m/a/magento2.png',
                    'thumbnail' => '/m/a/magento3.png',
                    'swatch_image' => '/m/a/magento4.png',
                ],
                '/m/a/magento1.png'
            ],
            [
                [
                    'small_image' => '/m/a/magento4.png',
                    'thumbnail' => '/m/a/magento5.png',
                    'swatch_image' => '/m/a/magento6.png',
                ],
                '/m/a/magento4.png'
            ],
            [
                [],
                ''
            ],
        ];
    }

    protected function getSwatchAttributes()
    {
        $this->getAttributesFromConfigurable();
        $returnFromProvideMethod = [$this->attributeMock];
        $this->swatchAttributesProviderMock
            ->method('provide')
            ->with($this->productMock)
            ->willReturn($returnFromProvideMethod);
    }

    /**
     * @param array $attributes
     * @param array $imageTypes
     */
    protected function getUsedProducts(array $attributes, array $imageTypes)
    {
        $this->productMock
            ->expects($this->atLeastOnce())
            ->method('getTypeInstance')
            ->willReturn($this->configurableMock);

        $simpleProducts = [];
        for ($i = 0; $i < 2; $i++) {
            $simpleProduct = $this->getMock(
                \Magento\Catalog\Model\Product::class,
                ['hasData', 'getMediaGalleryEntries'],
                [],
                '',
                false
            );
            $simpleProduct->setData($attributes);

            $mediaGalleryEntries = [];
            foreach (array_keys($imageTypes) as $mediaType) {
                $mediaGalleryEntryMock = $this->getMockBuilder(
                    \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
                )->getMock();
                $mediaGalleryEntryMock->expects($this->any())
                    ->method('isDisabled')
                    ->willReturn(false);
                $mediaGalleryEntryMock->expects($this->any())
                    ->method('getTypes')
                    ->willReturn([$mediaType]);

                $mediaGalleryEntries[] = $mediaGalleryEntryMock;
            }
            $simpleProduct->expects($this->any())
                ->method('getMediaGalleryEntries')
                ->willReturn($mediaGalleryEntries);

            $simpleProducts[] = $simpleProduct;
        }

        $this->configurableMock->expects($this->once())
            ->method('getUsedProducts')
            ->with($this->productMock)
            ->willReturn($simpleProducts);

    }

    protected function getAttributesFromConfigurable()
    {
        $confAttribute = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class,
            [],
            [],
            '',
            false
        );

        $this->configurableMock
            ->expects($this->any())
            ->method('getConfigurableAttributes')
            ->with($this->productMock)
            ->willReturn([$confAttribute, $confAttribute]);

        $confAttribute
            ->expects($this->any())
            ->method('__call')
            ->with('getProductAttribute')
            ->willReturn($this->attributeMock);
    }

    protected function prepareVariationCollection()
    {
        $this->productCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->productCollectionMock);

        $this->addfilterByParent();
    }

    protected function addfilterByParent()
    {
        $this->productCollectionMock
            ->method('getTable')
            ->with('catalog_product_relation')
            ->willReturn('catalog_product_relation');

        $zendDbSelectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);

        $this->productCollectionMock->method('getSelect')->willReturn($zendDbSelectMock);
        $zendDbSelectMock->method('join')->willReturn($zendDbSelectMock);
        $zendDbSelectMock->method('where')->willReturn($zendDbSelectMock);
    }

    /**
     * @return array
     */
    public function dataForCreateSwatchProduct()
    {
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        return [
            [
                $productMock,
                [
                    'image' => '',
                    'small_image' => '',
                    'thumbnail' => '',
                    'swatch_image' => '',
                ]
            ],
            [
                $productMock,
                [
                    'small_image' => 'img1.png',
                    'thumbnail' => 'img1.png',
                ]
            ],
            [
                $productMock,
                []
            ],
            [
                $productMock,
                [
                    'image' => 'img1.png',
                    'small_image' => 'img1.png',
                    'thumbnail' => 'img1.png',
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataForCreateSwatchProductByFallback()
    {
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);

        return [
            [
                95,
            ],
            [
                $productMock,
            ],
        ];
    }

    /**
     * @dataProvider dataForGettingSwatchAsArray
     */
    public function testGetSwatchAttributesAsArray($optionsArray, $attributeData, $expected)
    {
        $this->swatchAttributesProviderMock
            ->method('provide')
            ->with($this->productMock)
            ->willReturn([$this->attributeMock]);

        $storeId = 1;

        $this->attributeMock->method('setStoreId')->with($storeId)->will($this->returnSelf());
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $storeMock->method('getId')->willReturn($storeId);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        $this->attributeMock
            ->method('getData')
            ->withConsecutive(['additional_data'], [''])
            ->will($this->onConsecutiveCalls('', $attributeData));

        $sourceMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class,
            [],
            [],
            '',
            false
        );
        $sourceMock->expects($this->any())->method('getAllOptions')->with(false)->willReturn($optionsArray);
        $this->attributeMock->method('getSource')->willReturn($sourceMock);

        $result = $this->swatchHelperObject->getSwatchAttributesAsArray($this->productMock);
        $this->assertEquals($result, $expected);
    }

    /**
     * @return array
     */
    public function dataForGettingSwatchAsArray()
    {
        return [
            [
                [
                    ['value' => 45, 'label' => 'green'],
                    ['value' => 46, 'label' => 'yellow'],
                    ['value' => 47, 'label' => 'red'],
                    ['value' => 48, 'label' => 'blue'],
                ],
                [
                    'attribute_id' => 52
                ],
                [
                    52 => [
                        'attribute_id' => 52,
                        'options' => [
                            45 => 'green',
                            46 => 'yellow',
                            47 => 'red',
                            48 => 'blue',
                        ],
                    ]
                ],
            ],
            [
                [
                    ['value' => 45, 'label' => 'green'],
                    ['value' => 46, 'label' => 'yellow'],
                ],
                [
                    'attribute_id' => 324
                ],
                [
                    324 => [
                        'attribute_id' => 324,
                        'options' => [
                            45 => 'green',
                            46 => 'yellow',
                        ],
                    ]
                ],
            ],
        ];
    }

    public function testGetSwatchesByOptionsIdIf1()
    {
        $swatchMock = $this->getMock(\Magento\Swatches\Model\Swatch::class, [], [], '', false);

        $optionsData = [
            [
                'type' => 1,
                'store_id' => 1,
                'value' => '#324234',
                'option_id' => 35,
                'id' => 423,
            ],
        ];

        $swatchMock->expects($this->at(0))->method('offsetGet')->with('type')->willReturn(1);
        $swatchMock->expects($this->at(1))->method('offsetGet')->with('option_id')->willReturn(35);
        $swatchMock->expects($this->at(2))->method('getData')->with('')->willReturn($optionsData[0]);

        $swatchCollectionMock = $this->objectManager->getCollectionMock(
            \Magento\Swatches\Model\ResourceModel\Swatch\Collection::class,
            [
                $swatchMock,
            ]
        );
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->will($this->returnSelf());

        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn(1);

        $this->swatchHelperObject->getSwatchesByOptionsId([35]);
    }

    public function testGetSwatchesByOptionsIdIf2()
    {
        $swatchMock = $this->getMock(\Magento\Swatches\Model\Swatch::class, [], [], '', false);

        $optionsData = [
            [
                'type' => 0,
                'store_id' => 1,
                'value' => 'test',
                'option_id' => 35,
                'id' => 487,
            ],
            [
                'type' => 0,
                'store_id' => 1,
                'value' => 'test2',
                'option_id' => 36,
                'id' => 488,
            ]
        ];

        $swatchMock->expects($this->at(0))->method('offsetGet')->with('type')->willReturn(0);
        $swatchMock->expects($this->at(1))->method('offsetGet')->with('store_id')->willReturn(1);
        $swatchMock->expects($this->at(2))->method('offsetGet')->with('value')->willReturn('test');
        $swatchMock->expects($this->at(3))->method('offsetGet')->with('option_id')->willReturn(35);
        $swatchMock->expects($this->at(4))->method('getData')->with('')->willReturn($optionsData[0]);
        $swatchMock->expects($this->at(5))->method('offsetGet')->with('type')->willReturn(0);
        $swatchMock->expects($this->at(6))->method('offsetGet')->with('store_id')->willReturn(1);
        $swatchMock->expects($this->at(7))->method('offsetGet')->with('value')->willReturn('test2');
        $swatchMock->expects($this->at(8))->method('offsetGet')->with('option_id')->willReturn(36);
        $swatchMock->expects($this->at(9))->method('getData')->with('')->willReturn($optionsData[1]);

        $swatchCollectionMock = $this->objectManager->getCollectionMock(
            \Magento\Swatches\Model\ResourceModel\Swatch\Collection::class,
            [
                $swatchMock,
                $swatchMock,
            ]
        );
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->will($this->returnSelf());

        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn(1);

        $this->swatchHelperObject->getSwatchesByOptionsId([35]);
    }

    public function testGetSwatchesByOptionsIdIf3()
    {
        $swatchMock = $this->getMock(\Magento\Swatches\Model\Swatch::class, [], [], '', false);

        $optionsData = [
            'type' => 0,
            'store_id' => 0,
            'value' => 'test_test',
            'option_id' => 35,
            'id' => 423,
        ];

        $swatchMock->expects($this->at(0))->method('offsetGet')->with('type')->willReturn(0);
        $swatchMock->expects($this->at(1))->method('offsetGet')->with('store_id')->willReturn(0);
        $swatchMock->expects($this->at(2))->method('offsetGet')->with('store_id')->willReturn(0);
        $swatchMock->expects($this->at(3))->method('offsetGet')->with('option_id')->willReturn(35);
        $swatchMock->expects($this->at(4))->method('getData')->with('')->willReturn($optionsData);

        $swatchCollectionMock = $this->objectManager->getCollectionMock(
            \Magento\Swatches\Model\ResourceModel\Swatch\Collection::class,
            [
                $swatchMock,
            ]
        );
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->will($this->returnSelf());

        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn(1);

        $this->swatchHelperObject->getSwatchesByOptionsId([35]);
    }

    public function testIsProductHasSwatch()
    {
        $this->getSwatchAttributes();
        $result = $this->swatchHelperObject->isProductHasSwatch($this->productMock);
        $this->assertEquals(true, $result);
    }

    /**
     * @dataProvider dataIsVisualSwatch
     */
    public function testIsVisualSwatch($swatchType, $boolResult)
    {
        $this->attributeMock->method('hasData')->with('swatch_input_type')->willReturn(true);
        $this->attributeMock
            ->expects($this->once())
            ->method('getData')
            ->with('swatch_input_type')
            ->willReturn($swatchType);
        $result = $this->swatchHelperObject->isVisualSwatch($this->attributeMock);
        if ($boolResult) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @return array
     */
    public function dataIsVisualSwatch()
    {
        return [
            [
                'visual',
                true,
            ],
            [
                'some_other_type',
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataForIsVisualSwatchType
     */
    public function testIsVisualSwatchFalse($data, $count, $swatchType, $boolResult)
    {
        $this->attributeMock->method('hasData')->with('swatch_input_type')->willReturn(false);

        $this->attributeMock->expects($this->exactly(2))->method('getData')->withConsecutive(
            ['additional_data'],
            ['swatch_input_type']
        )->willReturnOnConsecutiveCalls(
            $data,
            $swatchType
        );

        $this->attributeMock
            ->expects($this->exactly($count['setData']))
            ->method('setData');

        $result = $this->swatchHelperObject->isVisualSwatch($this->attributeMock);
        if ($boolResult) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @return array
     */
    public function dataForIsVisualSwatchType()
    {
        $additionalData = [
            'swatch_input_type' => 'visual',
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];

        return [
            [
                serialize($additionalData),
                [
                    'getData' => 1,
                    'setData' => 0,
                ],
                'visual',
                true,
            ],
            [
                null,
                [
                    'getData' => 1,
                    'setData' => 0,
                ],
                'some_other_type',
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataForIsTextSwatchType
     */
    public function testIsTextSwatchFalse($data, $count, $swatchType, $boolResult)
    {
        $this->attributeMock->method('hasData')->with('swatch_input_type')->willReturn(false);

        $this->attributeMock->expects($this->exactly(2))->method('getData')->withConsecutive(
            ['additional_data'],
            ['swatch_input_type']
        )->willReturnOnConsecutiveCalls(
            $data,
            $swatchType
        );

        $this->attributeMock
            ->expects($this->exactly($count['setData']))
            ->method('setData');

        $result = $this->swatchHelperObject->isTextSwatch($this->attributeMock);
        if ($boolResult) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @return array
     */
    public function dataForIsTextSwatchType()
    {
        $additionalData = [
            'swatch_input_type' => 'text',
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];

        return [
            [
                serialize($additionalData),
                [
                    'getData' => 1,
                    'setData' => 0,
                ],
                'text',
                true,
            ],
            [
                null,
                [
                    'getData' => 1,
                    'setData' => 0,
                ],
                'some_other_type',
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataIsTextSwatch
     */
    public function testIsTextSwatch($swatchType, $boolResult)
    {
        $this->attributeMock->method('hasData')->with('swatch_input_type')->willReturn(true);
        $this->attributeMock
            ->expects($this->once())
            ->method('getData')
            ->with('swatch_input_type')
            ->willReturn($swatchType);
        $result = $this->swatchHelperObject->isTextSwatch($this->attributeMock);
        if ($boolResult) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @return array
     */
    public function dataIsTextSwatch()
    {
        return [
            [
                'text',
                true,
            ],
            [
                'some_other_type',
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataIsSwatchAttribute
     */
    public function testIsSwatchAttribute($times, $swatchType, $boolResult)
    {
        $this->attributeMock->method('hasData')->with('swatch_input_type')->willReturn(true);
        $this->attributeMock
            ->expects($this->exactly($times))
            ->method('getData')
            ->with('swatch_input_type')
            ->willReturn($swatchType);

        $result = $this->swatchHelperObject->isSwatchAttribute($this->attributeMock);
        if ($boolResult) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @return array
     */
    public function dataIsSwatchAttribute()
    {
        return [
            [
                1,
                'visual',
                true,
            ],
            [
                2,
                'text',
                true,
            ],
            [
                2,
                'some_other_type',
                false,
            ],
        ];
    }
}
