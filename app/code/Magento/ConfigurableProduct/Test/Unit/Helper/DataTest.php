<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Helper;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_imageHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_productMock;

    /**
     * @var UrlBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $imageUrlBuilder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->imageUrlBuilder = $this->getMockBuilder(UrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_imageHelperMock = $this->createMock(\Magento\Catalog\Helper\Image::class);
        $this->_productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $this->_model = $objectManager->getObject(
            \Magento\ConfigurableProduct\Helper\Data::class,
            [
                '_imageHelper' => $this->_imageHelperMock
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->_model, 'imageUrlBuilder', $this->imageUrlBuilder);
    }

    public function testGetAllowAttributes()
    {
        $typeInstanceMock = $this->createMock(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class);
        $typeInstanceMock->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->_productMock);

        $this->_productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);

        $this->_model->getAllowAttributes($this->_productMock);
    }

    /**
     * @param array $expected
     * @param array $data
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions(array $expected, array $data)
    {
        if (count($data['allowed_products'])) {
            $imageHelper1 = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
                ->disableOriginalConstructor()
                ->getMock();
            $imageHelper1->expects($this->any())
                ->method('getUrl')
                ->willReturn('http://example.com/base_img_url');

            $imageHelper2 = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
                ->disableOriginalConstructor()
                ->getMock();
            $imageHelper2->expects($this->any())
                ->method('getUrl')
                ->willReturn('http://example.com/base_img_url_2');

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

    /**
     * @return array
     */
    public function getOptionsDataProvider()
    {
        $currentProductMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getTypeInstance', '__wakeup']
        );
        $provider = [];
        $provider[] = [
            [],
            [
                'allowed_products' => [],
                'current_product_mock' => $currentProductMock,
            ],
        ];

        $attributesCount = 3;
        $attributes = [];
        for ($i = 1; $i < $attributesCount; $i++) {
            $attribute = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getProductAttribute']);
            $productAttribute = $this->createPartialMock(
                \Magento\Framework\DataObject::class,
                ['getId', 'getAttributeCode']
            );
            $productAttribute->expects($this->any())
                ->method('getId')
                ->willReturn('attribute_id_' . $i);
            $productAttribute->expects($this->any())
                ->method('getAttributeCode')
                ->willReturn('attribute_code_' . $i);
            $attribute->expects($this->any())
                ->method('getProductAttribute')
                ->willReturn($productAttribute);
            $attributes[] = $attribute;
        }
        $typeInstanceMock = $this->createMock(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class);
        $typeInstanceMock->expects($this->any())
            ->method('getConfigurableAttributes')
            ->willReturn($attributes);
        $currentProductMock->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($typeInstanceMock);
        $allowedProducts = [];
        for ($i = 1; $i <= 2; $i++) {
            $productMock = $this->createPartialMock(
                \Magento\Catalog\Model\Product::class,
                ['getData', 'getImage', 'getId', '__wakeup', 'getMediaGalleryImages', 'isSalable']
            );
            $productMock->expects($this->any())
                ->method('getData')
                ->willReturnCallback([$this, 'getDataCallback']);
            $productMock->expects($this->any())
                ->method('getId')
                ->willReturn('product_id_' . $i);
            $productMock
                ->expects($this->any())
                ->method('isSalable')
                ->willReturn(true);
            if ($i == 2) {
                $productMock->expects($this->any())
                    ->method('getImage')
                    ->willReturn(true);
            }
            $allowedProducts[] = $productMock;
        }
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
    public function getDataCallback($key)
    {
        $map = [];
        for ($k = 1; $k < 3; $k++) {
            $map['attribute_code_' . $k] = 'attribute_code_value_' . $k;
        }
        return $map[$key];
    }

    public function testGetGalleryImages()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getMediaGalleryImages'])
            ->getMockForAbstractClass();
        $productMock->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn($this->getImagesCollection());

        $this->imageUrlBuilder->expects($this->exactly(3))
            ->method('getUrl')
            ->withConsecutive(
                [
                    self::identicalTo('test_file'),
                    self::identicalTo('product_page_image_small')
                ],
                [
                    self::identicalTo('test_file'),
                    self::identicalTo('product_page_image_medium')
                ],
                [
                    self::identicalTo('test_file'),
                    self::identicalTo('product_page_image_large')
                ]
            )
            ->will(
                self::onConsecutiveCalls(
                    'testSmallImageUrl',
                    'testMediumImageUrl',
                    'testLargeImageUrl'
                )
            );
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
            \Magento\Framework\Data\Collection::class,
            $this->_model->getGalleryImages($productMock)
        );
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    private function getImagesCollection()
    {
        $collectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [
            new \Magento\Framework\DataObject(
                ['file' => 'test_file']
            ),
        ];

        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        return $collectionMock;
    }
}
