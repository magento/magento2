<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Helper;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Swatches\Model\ResourceModel\Swatch\Collection;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Swatches\Model\SwatchAttributeType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit\Framework\TestCase
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

    /** @var \PHPUnit_Framework_MockObject_MockObject|Attribute */
    protected $attributeMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager|\Magento\Swatches\Helper\Data */
    protected $swatchHelperObject;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepoMock;

    /** @var   \PHPUnit_Framework_MockObject_MockObject|MetadataPool */
    private $metaDataPoolMock;

    /**
     * @var SwatchAttributesProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $swatchAttributesProvider;

    /**
     * @var SwatchAttributeType|\PHPUnit_Framework_MockObject_MockObject
     */
    private $swatchTypeCheckerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->imageHelperMock = $this->createMock(\Magento\Catalog\Helper\Image::class);
        $this->productCollectionFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create']
        );
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productCollectionMock = $this->objectManager->getCollectionMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [
                $this->productMock,
                $this->productMock,
            ]
        );

        $this->configurableMock = $this->createMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class
        );
        $this->productModelFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create']
        );

        $this->productRepoMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->swatchCollectionFactoryMock = $this->createPartialMock(
            \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory::class,
            ['create']
        );

        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStoreId', 'getData', 'setData', 'getSource', 'hasData'])
            ->getMock();
        $this->metaDataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer = $this->createPartialMock(
            \Magento\Framework\Serialize\Serializer\Json::class,
            ['serialize', 'unserialize']
        );
        $serializer->expects($this->any())
            ->method('serialize')->willReturnCallback(function ($parameter) {
                return json_encode($parameter);
            });
        $serializer->expects($this->any())
            ->method('unserialize')->willReturnCallback(function ($parameter) {
                return json_decode($parameter, true);
            });

        $this->swatchAttributesProvider = $this->getMockBuilder(SwatchAttributesProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->swatchTypeCheckerMock = $this->getMockBuilder(SwatchAttributeType::class)
            ->disableOriginalConstructor()
            ->setMethods(['isVisualSwatch', 'isTextSwatch', 'isSwatchAttribute'])
            ->getMock();

        $this->swatchHelperObject = $this->objectManager->getObject(
            \Magento\Swatches\Helper\Data::class,
            [
                'productCollectionFactory' => $this->productCollectionFactoryMock,
                'configurable' => $this->configurableMock,
                'productRepository' => $this->productRepoMock,
                'storeManager' => $this->storeManagerMock,
                'swatchCollectionFactory' => $this->swatchCollectionFactoryMock,
                'imageHelper' => $this->imageHelperMock,
                'serializer' => $serializer,
                'swatchAttributesProvider' => $this->swatchAttributesProvider,
                'swatchTypeChecker' => $this->swatchTypeCheckerMock,
            ]
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->swatchHelperObject,
            'metadataPool',
            $this->metaDataPoolMock
        );
    }

    public function dataForAdditionalData()
    {
        $additionalData = [
            'swatch_input_type' => 'visual',
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];
        return [
            [
                json_encode($additionalData),
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

    public function dataForAssembleEavAttribute()
    {
        $additionalData = [
            'swatch_input_type' => 'visual',
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];
        return [
            [
                json_encode($additionalData),
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
        $this->getUsedProducts($imageTypes + $requiredAttributes);

        $result = $this->swatchHelperObject->loadFirstVariationWithSwatchImage($this->productMock, $requiredAttributes);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $result);
        }
    }

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
                ['color' => 31],
            ],
            [
                [
                    'image' => '/m/a/magento.png',
                    'small_image' => '/m/a/magento.png',
                    'thumbnail' => '/m/a/magento.png',
                ],
                false,
                ['size' => 31],
            ],
        ];
    }

    /**
     * @dataProvider dataForCreateSwatchProductByFallback
     */
    public function testLoadVariationByFallback($product)
    {
        $metadataMock = $this->createMock(\Magento\Framework\EntityManager\EntityMetadataInterface::class);
        $this->metaDataPoolMock->expects($this->once())->method('getMetadata')->willReturn($metadataMock);
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('id');

        $this->getSwatchAttributes($product);

        $this->prepareVariationCollection();

        $this->productCollectionMock->method('getFirstItem')->willReturn($this->productMock);
        $this->productMock->method('getData')->with('id')->willReturn(95);
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
        $this->getUsedProducts($imageTypes + $requiredAttributes);

        $result = $this->swatchHelperObject->loadFirstVariationWithImage($this->productMock, $requiredAttributes);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertInstanceOf(\Magento\Catalog\Model\Product::class, $result);
        }
    }

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
                ['color' => 31],
            ],
            [
                [
                    'small_image' => '/m/a/magento.png',
                    'thumbnail' => '/m/a/magento.png',
                    'swatch_image' => '/m/a/magento.png',
                ],
                false,
                ['size' => 31],
            ],
        ];
    }

    public function testLoadFirstVariationWithImageNoProduct()
    {
        $this->swatchAttributesProvider
            ->method('provide')
            ->with($this->productMock)
            ->willReturn([]);
        $result = $this->swatchHelperObject->loadVariationByFallback($this->productMock, ['color' => 31]);

        $this->assertFalse($result);
    }

    public function testLoadVariationByFallbackWithoutProduct()
    {
        $this->swatchAttributesProvider
            ->method('provide')
            ->with($this->productMock)
            ->willReturn([]);
        $result = $this->swatchHelperObject->loadFirstVariationWithImage($this->productMock, ['color' => 31]);

        $this->assertFalse($result);
    }

    /**
     * @dataProvider dataForMediaGallery
     */
    public function testGetProductMediaGallery($mediaGallery, $image)
    {
        $this->productMock->expects($this->once())->method('getMediaAttributeValues')->willReturn($mediaGallery);
        $this->productMock->expects($this->any())->method('getId')->willReturn(95);

        $this->imageHelperMock->expects($this->any())
            ->method('init')
            ->willReturnMap([
                [$this->productMock, 'product_page_image_large', [], $this->imageHelperMock],
                [$this->productMock, 'product_page_image_medium', [], $this->imageHelperMock],
                [$this->productMock, 'product_page_image_small', [], $this->imageHelperMock],
            ]);

        $this->imageHelperMock->expects($this->any())
            ->method('setImageFile')
            ->with($image)
            ->willReturnSelf();
        $this->imageHelperMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://full_path_to_image/magento1.png');

        $this->productRepoMock->expects($this->any())
            ->method('getById')
            ->with(95)
            ->willReturn($this->productMock);

        $mediaObject = $this->createMock(\Magento\Framework\DataObject::class);
        $iterator = new \ArrayIterator([$mediaObject]);
        $mediaCollectionMock = $this->createMock(\Magento\Framework\Data\Collection::class);
        $mediaCollectionMock->expects($this->any())->method('getIterator')->willReturn($iterator);
        $mediaObject->method('getData')->withConsecutive(
            ['value_id'],
            ['file']
        )->willReturnOnConsecutiveCalls(
            0,
            $image
        );
        $this->productMock->method('getMediaGalleryImages')->willReturn($mediaCollectionMock);

        $this->swatchHelperObject->getProductMediaGallery($this->productMock);
    }

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
        $this->swatchAttributesProvider
            ->method('provide')
            ->with($this->productMock)
            ->willReturn($returnFromProvideMethod);
    }

    protected function getUsedProducts(array $attributes)
    {
        $this->productMock
            ->expects($this->atLeastOnce())
            ->method('getTypeInstance')
            ->willReturn($this->configurableMock);

        $product1 = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['hasData']);
        $product1->setData($attributes);

        $product2 = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['hasData']);
        $product2->setData($attributes);

        $simpleProducts = [$product2, $product1];

        $this->configurableMock->expects($this->once())->method('getUsedProducts')->with($this->productMock)
            ->willReturn($simpleProducts);
    }

    protected function getAttributesFromConfigurable()
    {
        $confAttribute = $this->createMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute::class
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

        $zendDbSelectMock = $this->createMock(\Magento\Framework\DB\Select::class);

        $this->productCollectionMock->method('getSelect')->willReturn($zendDbSelectMock);
        $zendDbSelectMock->method('join')->willReturn($zendDbSelectMock);
        $zendDbSelectMock->method('where')->willReturn($zendDbSelectMock);
    }

    public function dataForCreateSwatchProduct()
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

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

    public function dataForCreateSwatchProductByFallback()
    {
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

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
        $this->swatchAttributesProvider
            ->method('provide')
            ->with($this->productMock)
            ->willReturn([$this->attributeMock]);

        $storeId = 1;

        $this->attributeMock->method('setStoreId')->with($storeId)->will($this->returnSelf());
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->method('getId')->willReturn($storeId);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        $this->attributeMock->method('getData')->with('')->willReturn($attributeData);

        $sourceMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class);
        $sourceMock->expects($this->any())->method('getAllOptions')->with(false)->willReturn($optionsArray);
        $this->attributeMock->method('getSource')->willReturn($sourceMock);

        $result = $this->swatchHelperObject->getSwatchAttributesAsArray($this->productMock);
        $this->assertEquals($result, $expected);
    }

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
        $swatchMock = $this->createMock(\Magento\Swatches\Model\Swatch::class);

        $optionsData = [
            [
                'type' => 1,
                'store_id' => 1,
                'value' => '#324234',
                'option_id' => 35,
                'id' => 423,
            ],
            [
                'type' => 0,
                'store_id' => 0,
                'value' => 'test2',
                'option_id' => 35,
                'id' => 424,
            ]
        ];

        $swatchMock->expects($this->at(0))->method('offsetGet')->with('type')
            ->willReturn($optionsData[0]['type']);
        $swatchMock->expects($this->at(1))->method('offsetGet')->with('option_id')
            ->willReturn($optionsData[0]['option_id']);
        $swatchMock->expects($this->at(2))->method('getData')->with('')
            ->willReturn($optionsData[0]);
        $swatchMock->expects($this->at(3))->method('offsetGet')->with('type')
            ->willReturn($optionsData[1]['type']);
        $swatchMock->expects($this->at(4))->method('offsetGet')->with('store_id')
            ->willReturn($optionsData[1]['store_id']);
        $swatchMock->expects($this->at(5))->method('offsetGet')->with('store_id')
            ->willReturn($optionsData[1]['store_id']);
        $swatchMock->expects($this->at(6))->method('offsetGet')->with('option_id')
            ->willReturn($optionsData[1]['option_id']);
        $swatchMock->expects($this->at(7))->method('getData')->with('')
            ->willReturn($optionsData[1]);

        $swatchCollectionMock = $this->objectManager
            ->getCollectionMock(Collection::class, [$swatchMock, $swatchMock]);
        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->will($this->returnSelf());
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn(1);

        $this->swatchHelperObject->getSwatchesByOptionsId([35]);
    }

    public function testGetSwatchesByOptionsIdIf2()
    {
        $swatchMock = $this->createMock(\Magento\Swatches\Model\Swatch::class);

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
            Collection::class,
            [
                $swatchMock,
                $swatchMock,
            ]
        );
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->will($this->returnSelf());

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn(1);

        $this->swatchHelperObject->getSwatchesByOptionsId([35]);
    }

    public function testGetSwatchesByOptionsIdIf3()
    {
        $swatchMock = $this->createMock(\Magento\Swatches\Model\Swatch::class);

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
            Collection::class,
            [
                $swatchMock,
            ]
        );
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->will($this->returnSelf());

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
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
     * @param bool $boolResult
     * @return void
     * @dataProvider dataIsSwatch
     */
    public function testIsVisualSwatch(bool $boolResult) : void
    {
        $this->swatchTypeCheckerMock
            ->expects($this->once())
            ->method('isVisualSwatch')
            ->with($this->attributeMock)
            ->willReturn($boolResult);
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
    public function dataIsSwatch() : array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param bool $boolResult
     * @return void
     * @dataProvider dataIsSwatch
     */
    public function testIsTextSwatch(bool $boolResult) : void
    {
        $this->swatchTypeCheckerMock
            ->expects($this->once())
            ->method('isTextSwatch')
            ->with($this->attributeMock)
            ->willReturn($boolResult);

        $result = $this->swatchHelperObject->isTextSwatch($this->attributeMock);

        if ($boolResult) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @param bool $boolResult
     * @return void
     * @dataProvider dataIsSwatch
     */
    public function testIsSwatchAttribute(bool $boolResult) : void
    {
        $this->swatchTypeCheckerMock
            ->expects($this->once())
            ->method('isSwatchAttribute')
            ->with($this->attributeMock)
            ->willReturn($boolResult);

        $result = $this->swatchHelperObject->isSwatchAttribute($this->attributeMock);
        if ($boolResult) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }
}
