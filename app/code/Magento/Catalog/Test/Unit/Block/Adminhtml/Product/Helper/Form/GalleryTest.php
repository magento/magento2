<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GalleryTest extends TestCase
{
    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var Gallery|MockObject
     */
    protected $gallery;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Form|MockObject
     */
    protected $formMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private $dataPersistorMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(Registry::class);
        $this->productMock = $this->createPartialMock(Product::class, ['getData']);
        $this->formMock = $this->createMock(Form::class);
        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        $this->objectManager = new ObjectManager($this);
        $this->gallery = $this->objectManager->getObject(
            Gallery::class,
            [
                'registry' => $this->registryMock,
                'form' => $this->formMock,
                'dataPersistor' => $this->dataPersistorMock
            ]
        );
    }

    public function testGetImages()
    {
        $mediaGallery = [
            'images' => [
                [
                    'value_id' => '1',
                    'file' => 'image_1.jpg',
                    'media_type' => 'image',
                ] ,
                [
                    'value_id' => '2',
                    'file' => 'image_2.jpg',
                    'media_type' => 'image',
                ]
            ]
        ];
        $this->registryMock->expects($this->once())->method('registry')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getData')->willReturn($mediaGallery);

        $this->assertSame($mediaGallery, $this->gallery->getImages());
    }

    /**
     * Test getImages() will try get data from data persistor, if it's absent in registry.
     *
     * @return void
     */
    public function testGetImagesWithDataPersistor()
    {
        $product = [
            'product' => [
                'media_gallery' => [
                    'images' => [
                        [
                            'value_id' => '1',
                            'file' => 'image_1.jpg',
                            'media_type' => 'image',
                        ],
                        [
                            'value_id' => '2',
                            'file' => 'image_2.jpg',
                            'media_type' => 'image',
                        ],
                    ],
                ],
            ],
        ];
        $this->registryMock->expects($this->once())->method('registry')->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getData')->willReturn(null);
        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with($this->identicalTo('catalog_product'))
            ->willReturn($product);

        $this->assertSame($product['product']['media_gallery'], $this->gallery->getImages());
    }

    /**
     * Test get image value from data persistor in case it's absent in product from registry.
     *
     * @return void
     */
    public function testGetImageValue()
    {
        $product = [
            'product' => [
                'media_gallery' => [
                    'images' => [
                        'value_id' => '1',
                        'file' => 'image_1.jpg',
                        'media_type' => 'image',
                    ],
                ],
                'small' => 'testSmallImage',
                'thumbnail' => 'testThumbnail'
            ]
        ];
        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with($this->identicalTo('catalog_product'))
            ->willReturn($product);
        $this->assertSame($product['product']['small'], $this->gallery->getImageValue('small'));
    }

    public function testGetDataObject()
    {
        $this->registryMock->expects($this->once())->method('registry')->willReturn($this->productMock);

        $this->assertSame($this->productMock, $this->gallery->getDataObject());
    }

    public function testGetAttributeFieldName()
    {
        $name = 'product[image]';

        $attribute = $this->createMock(Attribute::class);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn('image');

        $this->formMock->expects($this->once())->method('addSuffixToName')->willReturn($name);

        $this->assertSame($name, $this->gallery->getAttributeFieldName($attribute));
    }
}
