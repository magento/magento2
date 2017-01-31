<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Unit\Model\Product\Attribute\Media;

use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;

class ExternalVideoEntryConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     * |\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory
     */
    protected $mediaGalleryEntryFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     * |\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface
     */
    protected $mediaGalleryEntryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\DataObjectHelper */
    protected $dataObjectHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\Data\VideoContentInterfaceFactory */
    protected $videoEntryFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\Data\VideoContentInterface */
    protected $videoEntryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     * |\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory
     */
    protected $mediaGalleryEntryExtensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     * |\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory
     */
    protected $mediaGalleryEntryExtensionMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * |\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter
     */
    protected $modelObject;

    protected function setUp()
    {
        $this->mediaGalleryEntryFactoryMock =
            $this->getMock(
                '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory',
                ['create'],
                [],
                '',
                false
            );

        $this->mediaGalleryEntryMock =
            $this->getMock(
                '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface',
                [
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
                ],
                [],
                '',
                false
            );

        $this->mediaGalleryEntryFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->mediaGalleryEntryMock
        );

        $this->dataObjectHelperMock = $this->getMock('\Magento\Framework\Api\DataObjectHelper', [], [], '', false);

        $this->videoEntryFactoryMock =
            $this->getMock('\Magento\Framework\Api\Data\VideoContentInterfaceFactory', ['create'], [], '', false);

        $this->videoEntryMock = $this->getMock('\Magento\Framework\Api\Data\VideoContentInterface', [], [], '', false);

        $this->videoEntryFactoryMock->expects($this->any())->method('create')->willReturn($this->videoEntryMock);

        $this->mediaGalleryEntryExtensionFactoryMock =
            $this->getMock(
                '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory',
                ['create'],
                [],
                '',
                false
            );

        $this->mediaGalleryEntryExtensionMock = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtension',
            ['setVideoContent', 'getVideoContent', 'getVideoProvider'],
            [],
            '',
            false
        );

        $this->mediaGalleryEntryExtensionMock->expects($this->any())->method('setVideoContent')->willReturn(null);
        $this->mediaGalleryEntryExtensionFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->mediaGalleryEntryExtensionMock
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->modelObject = $objectManager->getObject(
            '\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter',
            [
                'mediaGalleryEntryFactory' => $this->mediaGalleryEntryFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'videoEntryFactory' => $this->videoEntryFactoryMock,
                'mediaGalleryEntryExtensionFactory' => $this->mediaGalleryEntryExtensionFactoryMock
            ]
        );
    }

    public function testGetMediaEntryType()
    {
        $this->assertEquals($this->modelObject->getMediaEntryType(), 'external-video');
    }

    public function testConvertTo()
    {
        /** @var  $product \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product */
        $product = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);

        $rowData = [
            'value_id' => '4',
            'file' => '/i/n/index111111.jpg',
            'media_type' => ExternalVideoEntryConverter::MEDIA_TYPE_CODE,
            'entity_id' => '1',
            'label' => '',
            'position' => '3',
            'disabled' => '0',
            'label_default' => null,
            'position_default' => '3',
            'disabled_default' => '0',
            'video_provider' => null,
            'video_url' => 'https://www.youtube.com/watch?v=abcdefghij',
            'video_title' => '111',
            'video_description' => null,
            'video_metadata' => null,
        ];

        $productImages = [
            'image' => '/s/a/sample_3.jpg',
            'small_image' => '/s/a/sample-1_1.jpg',
            'thumbnail' => '/s/a/sample-1_1.jpg',
            'swatch_image' => '/s/a/sample_3.jpg',
        ];

        $product->expects($this->once())->method('getMediaAttributeValues')->willReturn($productImages);

        $this->mediaGalleryEntryMock->expects($this->once())->method('setExtensionAttributes')->will(
            $this->returnSelf()
        );

        $this->modelObject->convertTo($product, $rowData);
    }

    public function testConvertFrom()
    {
        $this->mediaGalleryEntryMock->expects($this->once())->method('getId')->willReturn('4');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getFile')->willReturn('/i/n/index111111.jpg');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getLabel')->willReturn('Some Label');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getPosition')->willReturn('3');
        $this->mediaGalleryEntryMock->expects($this->once())->method('isDisabled')->willReturn('0');
        $this->mediaGalleryEntryMock->expects($this->once())->method('getTypes')->willReturn([]);
        $this->mediaGalleryEntryMock->expects($this->once())->method('getContent')->willReturn(null);

        $this->mediaGalleryEntryMock->expects($this->once())->method('getExtensionAttributes')->willReturn(
            $this->mediaGalleryEntryExtensionMock
        );

        $videoContentMock =
            $this->getMock('Magento\ProductVideo\Model\Product\Attribute\Media\VideoEntry', [], [], '', false);

        $videoContentMock->expects($this->once())->method('getVideoProvider')->willReturn('youtube');
        $videoContentMock->expects($this->once())->method('getVideoUrl')->willReturn(
            'https://www.youtube.com/watch?v=abcdefghij'
        );
        $videoContentMock->expects($this->once())->method('getVideoTitle')->willReturn('Some video title');
        $videoContentMock->expects($this->once())->method('getVideoDescription')->willReturn('Some video description');
        $videoContentMock->expects($this->once())->method('getVideoMetadata')->willReturn('Meta data');

        $this->mediaGalleryEntryExtensionMock->expects($this->once())->method('getVideoContent')->willReturn(
            $videoContentMock
        );

        $expectedResult = [
            'value_id' => '4',
            'file' => '/i/n/index111111.jpg',
            'label' => 'Some Label',
            'position' => '3',
            'disabled' => '0',
            'types' => [],
            'media_type' => null,
            'content' => null,
            'video_provider' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=abcdefghij',
            'video_title' => 'Some video title',
            'video_description' => 'Some video description',
            'video_metadata' => 'Meta data',
        ];

        $result = $this->modelObject->convertFrom($this->mediaGalleryEntryMock);
        $this->assertEquals($expectedResult, $result);
    }
}
