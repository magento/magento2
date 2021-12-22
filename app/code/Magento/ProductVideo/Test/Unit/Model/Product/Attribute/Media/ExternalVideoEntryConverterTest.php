<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductVideo\Test\Unit\Model\Product\Attribute\Media;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtension;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\Data\VideoContentInterface;
use Magento\Framework\Api\Data\VideoContentInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use Magento\ProductVideo\Model\Product\Attribute\Media\VideoEntry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExternalVideoEntryConverterTest extends TestCase
{
    /** @var MockObject|ProductAttributeMediaGalleryEntryInterfaceFactory */
    private $mediaGalleryEntryFactoryMock;

    /** @var MockObject|ProductAttributeMediaGalleryEntryInterface */
    private $mediaGalleryEntryMock;

    /** @var MockObject|DataObjectHelper */
    private $dataObjectHelperMock;

    /** @var MockObject|VideoContentInterfaceFactory */
    private $videoEntryFactoryMock;

    /** @var MockObject|VideoContentInterface */
    private $videoEntryMock;

    /** @var MockObject|ProductAttributeMediaGalleryEntryExtensionFactory */
    private $mediaGalleryEntryExtensionFactoryMock;

    /** @var MockObject|ProductAttributeMediaGalleryEntryExtension */
    private $mediaGalleryEntryExtensionMock;

    /**
     * @var ObjectManager|ExternalVideoEntryConverter
     */
    protected $modelObject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->mediaGalleryEntryFactoryMock = $this->createPartialMock(
            ProductAttributeMediaGalleryEntryInterfaceFactory::class,
            ['create']
        );

        $this->mediaGalleryEntryMock =
            $this->createPartialMock(
                ProductAttributeMediaGalleryEntryInterface::class,
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
                ]
            );

        $this->mediaGalleryEntryFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->mediaGalleryEntryMock
        );

        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);

        $this->videoEntryFactoryMock =
            $this->createPartialMock(VideoContentInterfaceFactory::class, ['create']);

        $this->videoEntryMock = $this->getMockForAbstractClass(VideoContentInterface::class);

        $this->videoEntryFactoryMock->expects($this->any())->method('create')->willReturn($this->videoEntryMock);

        $this->mediaGalleryEntryExtensionFactoryMock =
            $this->createPartialMock(
                ProductAttributeMediaGalleryEntryExtensionFactory::class,
                ['create']
            );

        $this->mediaGalleryEntryExtensionMock = $this->getProductAttributeMediaGalleryEntryExtensionMock();

        $this->mediaGalleryEntryExtensionMock->expects($this->any())->method('setVideoContent')->willReturn(null);
        $this->mediaGalleryEntryExtensionFactoryMock->expects($this->any())->method('create')->willReturn(
            $this->mediaGalleryEntryExtensionMock
        );

        $objectManager = new ObjectManager($this);

        $this->modelObject = $objectManager->getObject(
            ExternalVideoEntryConverter::class,
            [
                'mediaGalleryEntryFactory' => $this->mediaGalleryEntryFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'videoEntryFactory' => $this->videoEntryFactoryMock,
                'mediaGalleryEntryExtensionFactory' => $this->mediaGalleryEntryExtensionFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetMediaEntryType(): void
    {
        $this->assertEquals($this->modelObject->getMediaEntryType(), 'external-video');
    }

    /**
     * @return void
     */
    public function testConvertTo(): void
    {
        /** @var  MockObject|Product $product */
        $product = $this->createMock(Product::class);

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
            'video_metadata' => null
        ];

        $productImages = [
            'image' => '/s/a/sample_3.jpg',
            'small_image' => '/s/a/sample-1_1.jpg',
            'thumbnail' => '/s/a/sample-1_1.jpg',
            'swatch_image' => '/s/a/sample_3.jpg'
        ];

        $product->expects($this->once())->method('getMediaAttributeValues')->willReturn($productImages);

        $this->mediaGalleryEntryMock->expects($this->once())->method('setExtensionAttributes')->willReturnSelf();

        $this->modelObject->convertTo($product, $rowData);
    }

    /**
     * @return void
     */
    public function testConvertFrom(): void
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
            $this->createMock(VideoEntry::class);

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
            'video_metadata' => 'Meta data'
        ];

        $result = $this->modelObject->convertFrom($this->mediaGalleryEntryMock);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Build ProductAttributeMediaGalleryEntryExtension mock.
     *
     * @return MockObject
     */
    private function getProductAttributeMediaGalleryEntryExtensionMock(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(ProductAttributeMediaGalleryEntryExtension::class)
            ->disableOriginalConstructor();
        try {
            $mockBuilder->addMethods(
                [
                    'setVideoContent',
                    'getVideoContent',
                    'getVideoProvider'
                ]
            );
        } catch (RuntimeException $e) {
            // ProductAttributeMediaGalleryEntryExtension already generated and has all necessary methods.
        }

        return $mockBuilder->getMock();
    }
}
