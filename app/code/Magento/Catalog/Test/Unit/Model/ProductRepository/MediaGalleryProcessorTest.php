<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ProductRepository;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for ProductRepository/MediaGalleryProcessor.
 */
class MediaGalleryProcessorTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var MediaGalleryProcessor
     */
    private $model;

    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $processor;

    /**
     * @var ImageContentInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contentFactory;

    /**
     * @var ImageProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageProcessor;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'hasGalleryAttribute',
                'getMediaConfig',
                'getMediaAttributes',
                'getMediaGalleryEntries',
            ]
        );
        $this->product->expects($this->any())
            ->method('hasGalleryAttribute')
            ->willReturn(true);
        $this->processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentFactory = $this->getMockBuilder(ImageContentInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->imageProcessor = $this->getMockBuilder(ImageProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            MediaGalleryProcessor::class,
            [
                'processor' => $this->processor,
                'contentFactory' => $this->contentFactory,
                'imageProcessor' => $this->imageProcessor,
            ]
        );
    }

    /**
     * Test add image.
     *
     * @return void
     */
    public function testProcessWithNewMediaEntry()
    {
        $mediaGalleryEntries = [
            [
                'value_id' => null,
                'label' => 'label_text',
                'position' => 10,
                'disabled' => false,
                'types' => ['image', 'small_image'],
                'content' => [
                    ImageContentInterface::NAME => 'filename',
                    ImageContentInterface::TYPE => 'image/jpeg',
                    ImageContentInterface::BASE64_ENCODED_DATA => 'encoded_content',
                ],
                'media_type' => 'media_type',
            ],
        ];

        //setup media attribute backend.
        $mediaTmpPath = '/tmp';
        $absolutePath = '/a/b/filename.jpg';
        $mediaConfigMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Media\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mediaConfigMock->expects($this->once())
            ->method('getTmpMediaShortUrl')
            ->with($absolutePath)
            ->willReturn($mediaTmpPath . $absolutePath);
        $this->product->setData('media_gallery', ['images' => $mediaGalleryEntries]);
        $this->product->expects($this->any())
            ->method('getMediaAttributes')
            ->willReturn(['image' => 'imageAttribute', 'small_image' => 'small_image_attribute']);
        $this->product->expects($this->once())
            ->method('getMediaConfig')
            ->willReturn($mediaConfigMock);
        $this->processor->expects($this->once())->method('clearMediaAttribute')
            ->with($this->product, ['image', 'small_image']);

        //verify new entries.
        $contentDataObject = $this->getMockBuilder(\Magento\Framework\Api\ImageContent::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->contentFactory->expects($this->once())
            ->method('create')
            ->willReturn($contentDataObject);

        $this->imageProcessor->expects($this->once())
            ->method('processImageContent')
            ->willReturn($absolutePath);

        $imageFileUri = 'imageFileUri';
        $this->processor->expects($this->once())->method('addImage')
            ->with($this->product, $mediaTmpPath . $absolutePath, ['image', 'small_image'], true, false)
            ->willReturn($imageFileUri);
        $this->processor->expects($this->once())->method('updateImage')
            ->with(
                $this->product,
                $imageFileUri,
                [
                    'label' => 'label_text',
                    'position' => 10,
                    'disabled' => false,
                    'media_type' => 'media_type',
                ]
            );

        $this->model->processMediaGallery($this->product, $mediaGalleryEntries);
    }

    /**
     * Test update(delete) images.
     */
    public function testProcessExistingWithMediaGalleryEntries()
    {
        //update one entry, delete one entry.
        $newEntries = [
            [
                'id' => 5,
                'label' => 'new_label_text',
                'file' => 'filename1',
                'position' => 10,
                'disabled' => false,
                'types' => ['image', 'small_image'],
            ],
        ];

        $existingMediaGallery = [
            'images' => [
                [
                    'value_id' => 5,
                    'label' => 'label_text',
                    'file' => 'filename1',
                    'position' => 10,
                    'disabled' => true,
                ],
                [
                    'value_id' => 6, //will be deleted.
                    'file' => 'filename2',
                ],
            ],
        ];

        $expectedResult = [
            [
                'value_id' => 5,
                'id' => 5,
                'label' => 'new_label_text',
                'file' => 'filename1',
                'position' => 10,
                'disabled' => false,
                'types' => ['image', 'small_image'],
            ],
            [
                'value_id' => 6, //will be deleted.
                'file' => 'filename2',
                'removed' => true,
            ],
        ];

        $this->product->setData('media_gallery', $existingMediaGallery);
        $this->product->expects($this->any())
            ->method('getMediaAttributes')
            ->willReturn(['image' => 'filename1', 'small_image' => 'filename2']);

        $this->processor->expects($this->once())->method('clearMediaAttribute')
            ->with($this->product, ['image', 'small_image']);
        $this->processor->expects($this->once())
            ->method('setMediaAttribute')
            ->with($this->product, ['image', 'small_image'], 'filename1');
        $this->model->processMediaGallery($this->product, $newEntries);
        $this->assertEquals($expectedResult, $this->product->getMediaGallery('images'));
    }
}
