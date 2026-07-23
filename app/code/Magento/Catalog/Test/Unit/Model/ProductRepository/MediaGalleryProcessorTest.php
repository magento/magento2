<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ProductRepository;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\DeleteValidator;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ProductRepository\MediaGalleryProcessor;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageContent;
use Magento\Framework\Api\ImageProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaGalleryProcessorTest extends TestCase
{
    /**
     * @var MediaGalleryProcessor
     */
    private $galleryProcessor;

    /**
     * @var Processor|MockObject
     */
    private $processorMock;

    /**
     * @var ImageContentInterfaceFactory|MockObject
     */
    private $contentFactoryMock;

    /**
     * @var ImageProcessorInterface|MockObject
     */
    private $imageProcessorMock;

    /**
     * @var DeleteValidator|MockObject
     */
    private $deleteValidatorMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    protected function setUp(): void
    {
        $this->processorMock = $this->createMock(Processor::class);
        $this->contentFactoryMock = $this->createMock(ImageContentInterfaceFactory::class);
        $this->imageProcessorMock = $this->createMock(ImageProcessorInterface::class);
        $this->deleteValidatorMock = $this->createMock(DeleteValidator::class);
        $this->productMock = $this->createPartialMock(Product::class, ['getMediaConfig', 'hasGalleryAttribute']);

        $this->galleryProcessor = new MediaGalleryProcessor(
            $this->processorMock,
            $this->contentFactoryMock,
            $this->imageProcessorMock,
            $this->deleteValidatorMock
        );
    }

    /**
     * The media gallery array should not have "removed" key while adding the new entry
     *
     * @return void
     */
    public function testProcessMediaGallery(): void
    {
        $initialExitingEntry = [
            'value_id' => 5,
            "label" => "new_label_text",
            'file' => 'filename1',
            'position' => 10,
            'disabled' => false,
            'types' => ['image', 'small_image']
        ];
        $newEntriesData = [
            'images' => [
                $initialExitingEntry,
                [
                    'value_id' => null,
                    'label' => "label_text",
                    'position' => 10,
                    'disabled' => false,
                    'types' => ['image', 'small_image'],
                    'content' => [
                        'data' => [
                            ImageContentInterface::NAME => 'filename',
                            ImageContentInterface::TYPE => 'image/jpeg',
                            ImageContentInterface::BASE64_ENCODED_DATA => 'encoded_content'
                        ]
                    ],
                    'media_type' => 'media_type'
                ]
            ]
        ];
        $newExitingEntriesData = [
            'images' => [
                $initialExitingEntry,
                [
                    'value_id' => 6,
                    "label" => "label_text2",
                    'file' => 'filename2',
                    'position' => 10,
                    'disabled' => false,
                    'types' => ['image', 'small_image']
                ]
            ]
        ];
        $this->productMock->setData('media_gallery', $newExitingEntriesData);
        $this->productMock->setData(
            'media_attributes',
            ["image" => "imageAttribute", "small_image" => "small_image_attribute"]
        );
        $mediaTmpPath = '/tmp';
        $absolutePath = '/a/b/filename.jpg';
        $this->processorMock->expects($this->once())->method('clearMediaAttribute')
            ->with($this->productMock, ['image', 'small_image']);
        $mediaConfigMock = $this->createMock(Config::class);
        $mediaConfigMock->method('getBaseTmpMediaPath')->willReturn($mediaTmpPath);
        $mediaConfigMock->expects($this->once())->method('getTmpMediaShortUrl')->with($absolutePath)
            ->willReturn($mediaTmpPath . $absolutePath);
        $this->productMock->expects($this->once())->method('getMediaConfig')->willReturn($mediaConfigMock);
        $this->productMock->method('hasGalleryAttribute')->willReturn(true);
        //verify new entries
        $contentDataObject = $this->createMock(ImageContent::class);
        $contentDataObject->method('setName')->willReturnSelf();
        $contentDataObject->method('setBase64EncodedData')->willReturnSelf();
        $contentDataObject->method('setType')->willReturnSelf();
        $this->contentFactoryMock->expects($this->once())->method('create')->willReturn($contentDataObject);
        $this->imageProcessorMock->expects($this->once())->method('processImageContent')->willReturn($absolutePath);
        $imageFileUri = $mediaTmpPath . $absolutePath;
        $this->processorMock->expects($this->once())->method('addImage')
            ->willReturnCallback(
                function ($product, $imageFileUri) use ($newEntriesData) {
                    foreach ($product['media_gallery']['images'] as $entry) {
                        if (isset($entry['value_id'])) {
                            $this->assertArrayNotHasKey('removed', $entry);
                        }
                    }
                    $this->productMock->setData('media_gallery', $newEntriesData);
                    return $imageFileUri;
                }
            );
        $this->processorMock->expects($this->once())->method('updateImage')
            ->with(
                $this->productMock,
                $imageFileUri,
                [
                    'label' => 'label_text',
                    'position' => 10,
                    'disabled' => false,
                    'media_type' => 'media_type',
                ]
            );
        $this->galleryProcessor->processMediaGallery($this->productMock, $newEntriesData['images']);
    }
}
