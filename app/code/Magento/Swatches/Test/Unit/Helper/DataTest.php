<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Helper;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Model\ResourceModel\Swatch\Collection;
use Magento\Swatches\Model\SwatchAttributesProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /** @var MockObject|Image */
    protected $imageHelperMock;

    /** @var MockObject|CollectionFactory */
    protected $productCollectionFactoryMock;

    /** @var MockObject|\Magento\Catalog\Model\ResourceModel\Product\Collection */
    protected $productCollectionMock;

    /** @var MockObject|Configurable */
    protected $configurableMock;

    /** @var MockObject|ProductFactory */
    protected $productModelFactoryMock;

    /** @var MockObject|Product */
    protected $productMock;

    /** @var MockObject|StoreManager */
    protected $storeManagerMock;

    /** @var MockObject|\Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory */
    protected $swatchCollectionFactoryMock;

    /** @var MockObject|Attribute */
    protected $attributeMock;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var ObjectManager|Data */
    protected $swatchHelperObject;

    /** @var MockObject|ProductRepositoryInterface */
    protected $productRepoMock;

    /** @var   MockObject|MetadataPool */
    private $metaDataPoolMock;

    /**
     * @var SwatchAttributesProvider|MockObject
     */
    private $swatchAttributesProvider;

    /**
     * @var  MockObject|UrlBuilder
     */
    private $imageUrlBuilderMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->imageHelperMock = $this->createMock(Image::class);
        $this->productCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->productMock = $this->createMock(Product::class);
        $this->productCollectionMock = $this->objectManager->getCollectionMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [
                $this->productMock,
                $this->productMock,
            ]
        );

        $this->configurableMock = $this->createMock(
            Configurable::class
        );
        $this->productModelFactoryMock = $this->createPartialMock(
            ProductFactory::class,
            ['create']
        );

        $this->productRepoMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);

        $this->storeManagerMock = $this->createMock(StoreManager::class);
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
            Json::class,
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
        $this->imageUrlBuilderMock = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMock();

        $this->swatchHelperObject = $this->objectManager->getObject(
            Data::class,
            [
                'productCollectionFactory' => $this->productCollectionFactoryMock,
                'configurable' => $this->configurableMock,
                'productRepository' => $this->productRepoMock,
                'storeManager' => $this->storeManagerMock,
                'swatchCollectionFactory' => $this->swatchCollectionFactoryMock,
                'imageUrlBuilder' => $this->imageUrlBuilderMock,
                'serializer' => $serializer,
                'swatchAttributesProvider' => $this->swatchAttributesProvider,
            ]
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->swatchHelperObject,
            'metadataPool',
            $this->metaDataPoolMock
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
            ->willReturn($dataFromDb);

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
        $this->getSwatchAttributes();
        $this->getUsedProducts($imageTypes + $requiredAttributes, $imageTypes);

        $result = $this->swatchHelperObject->loadFirstVariationWithSwatchImage($this->productMock, $requiredAttributes);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertInstanceOf(Product::class, $result);
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
                Product::class,
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

    public function testLoadVariationByFallback()
    {
        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $this->metaDataPoolMock->expects($this->once())->method('getMetadata')->willReturn($metadataMock);
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('id');

        $this->getSwatchAttributes();

        $this->prepareVariationCollection();

        $this->productCollectionMock->method('getFirstItem')->willReturn($this->productMock);
        $this->productMock->method('getData')->with('id')->willReturn(95);
        $this->productModelFactoryMock->method('create')->willReturn($this->productMock);
        $this->productMock->method('load')->with(95)->willReturnSelf();

        $this->swatchHelperObject->loadVariationByFallback($this->productMock, ['color' => 31]);
    }

    /**
     * @dataProvider dataForVariationWithImage
     */
    public function testLoadFirstVariationWithImage($imageTypes, $expected, $requiredAttributes)
    {
        $this->getSwatchAttributes();
        $this->getUsedProducts($imageTypes + $requiredAttributes, $imageTypes);

        $result = $this->swatchHelperObject->loadFirstVariationWithImage($this->productMock, $requiredAttributes);

        if ($expected === false) {
            $this->assertFalse($result);
        } else {
            $this->assertInstanceOf(Product::class, $result);
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
                Product::class,
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
                ProductAttributeMediaGalleryEntryInterface::class
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
            $mediaUrls[] = [$mediaFile, 'product_swatch_image_large', 'http://full_path_to_image' . $mediaFile];
            $mediaUrls[] = [$mediaFile, 'product_swatch_image_medium' ,'http://full_path_to_image' . $mediaFile];
            $mediaUrls[] = [$mediaFile, 'product_swatch_image_small','http://full_path_to_image' . $mediaFile];
        }

        $this->productMock->expects($this->once())
            ->method('getMediaGalleryEntries')
            ->willReturn($mediaGalleryEntries);

        if ($mediaGallery) {
            $this->imageUrlBuilderMock->expects($this->any())
                ->method('getUrl')
                ->willReturnMap($mediaUrls);
        }

        $productMediaGallery = $this->swatchHelperObject->getProductMediaGallery($this->productMock);
        if ($mediaGallery) {
            $this->assertStringContainsString($image, $productMediaGallery['large']);
            $this->assertStringContainsString($image, $productMediaGallery['medium']);
            $this->assertStringContainsString($image, $productMediaGallery['small']);
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
                '/m/a/magento1.png',
            ],
            [
                [
                    'small_image' => '/m/a/magento4.png',
                    'thumbnail' => '/m/a/magento5.png',
                    'swatch_image' => '/m/a/magento6.png',
                ],
                '/m/a/magento4.png',
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
            $simpleProduct = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->setMethods(['hasData', 'getMediaGalleryEntries'])
                ->getMock();
            $simpleProduct->setData($attributes);

            $mediaGalleryEntries = [];
            foreach (array_keys($imageTypes) as $mediaType) {
                $mediaGalleryEntryMock = $this->getMockBuilder(
                    ProductAttributeMediaGalleryEntryInterface::class
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

        $zendDbSelectMock = $this->createMock(Select::class);

        $this->productCollectionMock->method('getSelect')->willReturn($zendDbSelectMock);
        $zendDbSelectMock->method('join')->willReturn($zendDbSelectMock);
        $zendDbSelectMock->method('where')->willReturn($zendDbSelectMock);
    }

    /**
     * @return array
     */
    public function dataForCreateSwatchProduct()
    {
        $productMock = $this->createMock(Product::class);

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
     * @dataProvider dataForGettingSwatchAsArray
     */
    public function testGetSwatchAttributesAsArray($optionsArray, $attributeData, $expected)
    {
        $this->swatchAttributesProvider
            ->method('provide')
            ->with($this->productMock)
            ->willReturn([$this->attributeMock]);

        $storeId = 1;

        $this->attributeMock->method('setStoreId')->with($storeId)->willReturnSelf();
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')->willReturn($storeId);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);

        $this->attributeMock->method('getData')->with('')->willReturn($attributeData);

        $sourceMock = $this->createMock(AbstractSource::class);
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
        //Simulate behaviour of \Magento\Swatches\Model\Swatch as array item
        $swatchMock = $this->createMock(\ArrayAccess::class);

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
        $swatchMock->expects($this->at(2))->method('offsetGet')->with('type')
            ->willReturn($optionsData[1]['type']);
        $swatchMock->expects($this->at(3))->method('offsetGet')->with('store_id')
            ->willReturn($optionsData[1]['store_id']);
        $swatchMock->expects($this->at(4))->method('offsetGet')->with('store_id')
            ->willReturn($optionsData[1]['store_id']);
        $swatchMock->expects($this->at(5))->method('offsetGet')->with('option_id')
            ->willReturn($optionsData[1]['option_id']);

        $swatchCollectionMock = $this->createMock(Collection::class);
        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->willReturnSelf();
        $swatchCollectionMock->expects($this->once())->method('getData')->willReturn([$swatchMock, $swatchMock]);
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn(1);

        $this->swatchHelperObject->getSwatchesByOptionsId([35]);
    }

    public function testGetSwatchesByOptionsIdIf2()
    {
        //Simulate behaviour of \Magento\Swatches\Model\Swatch as array item
        $swatchMock = $this->createMock(\ArrayAccess::class);

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
        // @codingStandardsIgnoreStart
        $swatchMock->expects($this->at(0))->method('offsetGet')->with('type')->willReturn($optionsData[0]['type']);
        $swatchMock->expects($this->at(1))->method('offsetGet')->with('store_id')->willReturn($optionsData[0]['store_id']);
        $swatchMock->expects($this->at(2))->method('offsetGet')->with('value')->willReturn($optionsData[0]['value']);
        $swatchMock->expects($this->at(3))->method('offsetGet')->with('option_id')->willReturn($optionsData[0]['option_id']);
        $swatchMock->expects($this->at(4))->method('offsetGet')->with('type')->willReturn($optionsData[1]['type']);
        $swatchMock->expects($this->at(5))->method('offsetGet')->with('store_id')->willReturn($optionsData[1]['store_id']);
        $swatchMock->expects($this->at(6))->method('offsetGet')->with('value')->willReturn($optionsData[1]['value']);
        $swatchMock->expects($this->at(7))->method('offsetGet')->with('option_id')->willReturn($optionsData[1]['option_id']);
        // @codingStandardsIgnoreEnd
        $swatchCollectionMock = $this->createMock(Collection::class);
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->willReturnSelf();
        $swatchCollectionMock->expects($this->once())->method('getData')->willReturn([$swatchMock, $swatchMock]);

        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn(1);

        $this->swatchHelperObject->getSwatchesByOptionsId([35]);
    }

    public function testGetSwatchesByOptionsIdIf3()
    {
        //Simulate behaviour of \Magento\Swatches\Model\Swatch as array item
        $swatchMock = $this->createMock(\ArrayAccess::class);

        $optionsData = [
            'type' => 0,
            'store_id' => 0,
            'value' => 'test_test',
            'option_id' => 35,
            'id' => 423,
        ];
        // @codingStandardsIgnoreStart
        $swatchMock->expects($this->at(0))->method('offsetGet')->with('type')->willReturn($optionsData['type']);
        $swatchMock->expects($this->at(1))->method('offsetGet')->with('store_id')->willReturn($optionsData['store_id']);
        $swatchMock->expects($this->at(2))->method('offsetGet')->with('store_id')->willReturn($optionsData['store_id']);
        $swatchMock->expects($this->at(3))->method('offsetGet')->with('option_id')->willReturn($optionsData['option_id']);
        // @codingStandardsIgnoreEnd
        $swatchCollectionMock = $this->createMock(Collection::class);
        $this->swatchCollectionFactoryMock->method('create')->willReturn($swatchCollectionMock);

        $swatchCollectionMock->method('addFilterByOptionsIds')->with([35])->willReturnSelf();
        $swatchCollectionMock->expects($this->once())->method('getData')->willReturn([$swatchMock]);

        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $storeMock->method('getId')->willReturn(1);

        $this->swatchHelperObject->getSwatchesByOptionsId([35]);
    }

    public function testIsProductHasSwatch()
    {
        $this->getSwatchAttributes();
        $result = $this->swatchHelperObject->isProductHasSwatch($this->productMock);
        $this->assertTrue($result);
    }
}
