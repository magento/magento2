<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Data|MockObject
     */
    protected $_model;

    /**
     * @var Image|MockObject
     */
    protected $_imageHelperMock;

    /**
     * @var Product|MockObject
     */
    protected $_productMock;

    /**
     * @var UrlBuilder|MockObject
     */
    protected $imageUrlBuilder;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->imageUrlBuilder = $this->createMock(UrlBuilder::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->_imageHelperMock = $this->createMock(Image::class);
        $this->_productMock = $this->createMock(Product::class);
        $this->_productMock->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $this->_model = $objectManager->getObject(
            Data::class,
            [
                '_imageHelper' => $this->_imageHelperMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->_model, 'imageUrlBuilder', $this->imageUrlBuilder);
    }

    public function testGetAllowAttributes()
    {
        $typeInstanceMock = $this->createMock(Configurable::class);
        $typeInstanceMock->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->_productMock);

        $this->_productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->_productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);

        $this->_model->getAllowAttributes($this->_productMock);
    }

    /**
     * @param array $expected
     * @param array $data
     */
    #[DataProvider('getOptionsDataProvider')]
    public function testGetOptions(array $expected, array $data)
    {
        if (!empty($data['allowed_products']) && is_callable($data['allowed_products'])) {
            $data['allowed_products'] = $data['allowed_products']($this);
        }
        if (!empty($data['current_product_mock']) && is_callable($data['current_product_mock'])) {
            $data['current_product_mock'] = $data['current_product_mock']($this);
        }
        if (count($data['allowed_products'])) {
            $imageHelper1 = $this->createMock(Image::class);
            $imageHelper1->method('getUrl')->willReturn('http://example.com/base_img_url');

            $imageHelper2 = $this->createMock(Image::class);
            $imageHelper2->method('getUrl')->willReturn('http://example.com/base_img_url_2');

            $this->_imageHelperMock->expects($this->any())
                ->method('init')
                ->willReturnMap(
                    [
                        [$data['current_product_mock'], 'product_page_image_large', [], $imageHelper1],
                        [$data['allowed_products'][0], 'product_page_image_large', [], $imageHelper1],
                        [$data['allowed_products'][1], 'product_page_image_large', [], $imageHelper2],
                    ]
                );
        }

        $this->assertEquals(
            $expected,
            $this->_model->getOptions($data['current_product_mock'], $data['allowed_products'])
        );
    }

    protected function getMockForProductClass()
    {
        $currentProductMock = $this->createPartialMock(
            Product::class,
            [
                'getTypeInstance',
                'getTypeId'
            ]
        );
        $provider = [];
        $provider[] = [
            [
                'canDisplayShowOutOfStockStatus' => false
            ],
            [
                'allowed_products' => [],
                'current_product_mock' => $currentProductMock,
            ],
        ];

        $attributesCount = 3;
        $attributes = [];
        for ($i = 1; $i < $attributesCount; $i++) {
            $productAttribute = $this->createPartialMockWithReflection(
                DataObject::class,
                ['getId', 'setId', 'getAttributeCode', 'setAttributeCode']
            );
            $productAttribute->method('getId')->willReturn('attribute_id_' . $i);
            $productAttribute->method('getAttributeCode')->willReturn('attribute_code_' . $i);

            $attribute = $this->createPartialMockWithReflection(
                DataObject::class,
                ['getProductAttribute', 'setProductAttribute']
            );
            $attribute->method('getProductAttribute')->willReturn($productAttribute);
            $attributes[] = $attribute;
        }
        $typeInstanceMock = $this->createMock(Configurable::class);
        $typeInstanceMock->method('getConfigurableAttributes')->willReturn($attributes);
        $currentProductMock->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $currentProductMock->method('getTypeInstance')->willReturn($typeInstanceMock);

        return $currentProductMock;
    }

    protected function getMockForAllowProductClass()
    {
        $allowedProducts = [];
        for ($i = 1; $i <= 2; $i++) {
            $productMock = $this->createPartialMock(
                Product::class,
                ['getData', 'getImage', 'getId', 'getMediaGalleryImages', 'isSalable']
            );
            $productMock->expects($this->any())
                ->method('getData')
                ->willReturnCallback([$this, 'getDataCallback']);
            $productMock->method('getId')->willReturn('product_id_' . $i);
            $productMock
                ->method('isSalable')->willReturn(true);
            if ($i == 2) {
                $productMock->method('getImage')->willReturn(true);
            }
            $allowedProducts[] = $productMock;
        }

        return $allowedProducts;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getOptionsDataProvider(): array
    {
        $currentProductMock = static fn (self $testCase) => $testCase->getMockForProductClass();
        $allowedProducts = static fn (self $testCase) => $testCase->getMockForAllowProductClass();
        $provider[] = [
            [
                'attribute_id_1' => [
                    'attribute_code_value_1' => ['product_id_1', 'product_id_2'],
                ],
                'index' => [
                    'product_id_1' => [
                        'attribute_id_1' => 'attribute_code_value_1',
                        'attribute_id_2' => 'attribute_code_value_2'
                    ],

                    'product_id_2' => [
                        'attribute_id_1' => 'attribute_code_value_1',
                        'attribute_id_2' => 'attribute_code_value_2'
                    ]

                ],
                'attribute_id_2' => [
                    'attribute_code_value_2' => ['product_id_1', 'product_id_2'],
                ],
                'canDisplayShowOutOfStockStatus' => false
            ],
            [
                'allowed_products' => $allowedProducts,
                'current_product_mock' => $currentProductMock,
            ],
        ];
        return $provider;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getDataCallback($key): string
    {
        $map = [];
        for ($k = 1; $k < 3; $k++) {
            $map['attribute_code_' . $k] = 'attribute_code_value_' . $k;
        }
        return $map[$key];
    }

    public function testGetGalleryImages()
    {
        $productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getMediaGalleryImages']
        );
        $productMock->method('getMediaGalleryImages')->willReturn($this->getImagesCollection());

        $this->imageUrlBuilder->expects($this->exactly(3))
            ->method('getUrl')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == 'test_file' && $arg2 == 'product_page_image_small') {
                    return 'testSmallImageUrl';
                } elseif ($arg1 == 'test_file' && $arg2 == 'product_page_image_medium') {
                    return 'testMediumImageUrl';
                } elseif ($arg1 == 'test_file' && $arg2 == 'product_page_image_large') {
                    return 'testLargeImageUrl';
                }
            });

        $this->_imageHelperMock->expects(self::never())
            ->method('setImageFile')
            ->with('test_file')
            ->willReturnSelf();
        $this->_imageHelperMock->expects(self::never())
            ->method('getUrl')
            ->willReturn('product_page_image_small_url');
        $this->_imageHelperMock->expects(self::never())
            ->method('getUrl')
            ->willReturn('product_page_image_medium_url');
        $this->_imageHelperMock->expects(self::never())
            ->method('getUrl')
            ->willReturn('product_page_image_large_url');

        $this->assertInstanceOf(
            Collection::class,
            $this->_model->getGalleryImages($productMock)
        );
    }

    /**
     * @return Collection
     */
    private function getImagesCollection(): MockObject
    {
        $collectionMock = $this->createMock(Collection::class);

        $items = [
            new DataObject(
                ['file' => 'test_file']
            ),
        ];

        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator($items));

        return $collectionMock;
    }
}
