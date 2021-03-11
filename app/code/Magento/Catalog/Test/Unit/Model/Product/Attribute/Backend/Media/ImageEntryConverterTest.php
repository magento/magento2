<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend\Media;

class ImageEntryConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     * |\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory
     */
    protected $mediaGalleryEntryFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     * |\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntry
     */
    protected $mediaGalleryEntryMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter
     * |\Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $modelObject;

    protected function setUp(): void
    {
        $this->mediaGalleryEntryFactoryMock =
            $this->createPartialMock(
                \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory::class,
                ['create']
            );

        $this->mediaGalleryEntryMock =
            $this->createPartialMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class, [
                    'getId',
                    'setId',
                    'getMediaType',
                    'setMediaType',
                    'getLabel',
                    'setLabel',
                    'getPosition',
                    'setPosition',
                    'isDisabled',
                    'setDisabled',
                    'getTypes',
                    'setTypes',
                    'getFile',
                    'setFile',
                    'getContent',
                    'setContent',
                    'getExtensionAttributes',
                    'setExtensionAttributes'
                ]);

        $this->mediaGalleryEntryFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->mediaGalleryEntryMock
        );

        $this->dataObjectHelperMock = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);

        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->modelObject = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter::class,
            [
                'mediaGalleryEntryFactory' => $this->mediaGalleryEntryFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock
            ]
        );
    }

    public function testGetMediaEntryType()
    {
        $this->assertEquals($this->modelObject->getMediaEntryType(), 'image');
    }

    public function testConvertTo()
    {
        $rowData = [
            'value_id' => '6',
            'file' => '/s/a/sample-1_1.jpg',
            'media_type' => 'image',
            'entity_id' => '1',
            'label' => '',
            'position' => '5',
            'disabled' => '0',
            'label_default' => null,
            'position_default' => '5',
            'disabled_default' => '0',
        ];

        $productImages = [
            'image' => '/s/a/sample_3.jpg',
            'small_image' => '/s/a/sample-1_1.jpg',
            'thumbnail' => '/s/a/sample-1_1.jpg',
            'swatch_image' => '/s/a/sample_3.jpg',
        ];

        $this->productMock->expects($this->any())->method('getMediaAttributeValues')->willReturn($productImages);

        $object = $this->modelObject->convertTo($this->productMock, $rowData);
        $this->assertNotNull($object);
    }

    public function testConvertFromNullContent()
    {
        $this->mediaGalleryEntryMock->expects($this->once())->method('getId')->willReturn('5');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getFile')->willReturn('/s/a/sample_3.jpg');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getLabel')->willReturn('');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getPosition')->willReturn('4');
        $this->mediaGalleryEntryMock->expects($this->once())->method('isDisabled')->willReturn('0');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getTypes')->willReturn(
            [
                0 => 'image',
                1 => 'swatch_image',
            ]
        );
        $this->mediaGalleryEntryMock->expects($this->once())->method('getContent')->willReturn(null);

        $expectedResult = [
            'value_id' => '5',
            'file' => '/s/a/sample_3.jpg',
            'label' => '',
            'position' => '4',
            'disabled' => '0',
            'types' => [
                    0 => 'image',
                    1 => 'swatch_image',
                ],
            'content' => null,
            'media_type' => null,
        ];

        $this->assertEquals($expectedResult, $this->modelObject->convertFrom($this->mediaGalleryEntryMock));
    }

    public function testConvertFrom()
    {
        $this->mediaGalleryEntryMock->expects($this->once())->method('getId')->willReturn('5');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getFile')->willReturn('/s/a/sample_3.jpg');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getLabel')->willReturn('');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getPosition')->willReturn('4');
        $this->mediaGalleryEntryMock->expects($this->once())->method('isDisabled')->willReturn('0');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getTypes')->willReturn(
            [
                0 => 'image',
                1 => 'swatch_image',
            ]
        );
        $imageContentInterface = $this->createMock(\Magento\Framework\Api\Data\ImageContentInterface::class);

        $imageContentInterface->expects($this->once())->method('getBase64EncodedData')->willReturn(
            base64_encode('some_content')
        );
        $imageContentInterface->expects($this->once())->method('getType')->willReturn('image/jpeg');
        $imageContentInterface->expects($this->once())->method('getName')->willReturn('/s/a/sample_3.jpg');

        $this->mediaGalleryEntryMock->expects($this->once())->method('getContent')->willReturn($imageContentInterface);

        $expectedResult = [
            'value_id' => '5',
            'file' => '/s/a/sample_3.jpg',
            'label' => '',
            'position' => '4',
            'disabled' => '0',
            'types' => [
                    0 => 'image',
                    1 => 'swatch_image',
                ],
            'content' => [
                'data' => [
                    'base64_encoded_data' => base64_encode('some_content'),
                    'type' => 'image/jpeg',
                    'name' => '/s/a/sample_3.jpg'
                ]
            ],
                'media_type' => null,
        ];

        $this->assertEquals($expectedResult, $this->modelObject->convertFrom($this->mediaGalleryEntryMock));
    }
}
