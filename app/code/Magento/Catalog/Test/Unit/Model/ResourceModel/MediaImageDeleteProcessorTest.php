<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\MediaImageDeleteProcessor;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Catalog\Model\ResourceModel\MediaImageDeleteProcessor
 */
class MediaImageDeleteProcessorTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Testable Object
     *
     * @var MediaImageDeleteProcessor
     */
    private $mediaImageDeleteProcessor;

    /**
     * @var ObjectManager|null
     */
    private $objectManager;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var MediaConfig|MockObject
     */
    private $imageConfig;

    /**
     * @var Filesystem|MockObject
     */
    private $mediaDirectory;

    /**
     * @var Processor|MockObject
     */
    private $imageProcessor;

    /**
     * @var Gallery|MockObject
     */
    private $productGallery;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->productMock = $this->createPartialMock(Product::class, ['getId', 'getMediaGalleryImages']);

        $this->imageConfig = $this->createPartialMock(
            MediaConfig::class,
            ['getBaseMediaUrl', 'getMediaUrl', 'getBaseMediaPath', 'getMediaPath']
        );

        $this->mediaDirectory = $this->createMock(WriteInterface::class);

        $this->imageProcessor = $this->createPartialMock(Processor::class, ['removeImage']);

        $this->productGallery = $this->createPartialMock(Gallery::class, ['deleteGallery', 'countImageUses']);

        $this->mediaImageDeleteProcessor = $this->objectManager->getObject(
            MediaImageDeleteProcessor::class,
            [
                'imageConfig' => $this->imageConfig,
                'mediaDirectory' => $this->mediaDirectory,
                'imageProcessor' => $this->imageProcessor,
                'productGallery' => $this->productGallery
            ]
        );
    }

    /**
     * Test mediaImageDeleteProcessor execute method
     *
     * @param int $productId
     * @param array $productImages
     * @param bool $isValidFile
     * @param bool $imageUsedBefore
     */
    #[DataProvider('executeCategoryProductMediaDeleteDataProvider')]
    public function testExecuteCategoryProductMediaDelete(
        int $productId,
        array $productImages,
        bool $isValidFile,
        bool $imageUsedBefore
    ): void {
        $this->productMock->method('getId')->willReturn($productId);

        $this->productMock->method('getMediaGalleryImages')->willReturn($productImages);

        $this->mediaDirectory->method('isFile')->willReturn($isValidFile);

        // Set up the getRelativePath behavior using a callback
        $this->mediaDirectory->method('getRelativePath')->willReturnCallback(
            function ($arg) use ($productImages) {
                if ($arg == $productImages[0]->getFile()) {
                    return $productImages[0]->getPath();
                } elseif ($arg == $productImages[1]->getFile()) {
                    return $productImages[1]->getPath();
                }
            }
        );

        $this->productGallery->method('countImageUses')->willReturn($imageUsedBefore);

        $this->productGallery->expects($this->any())
            ->method('deleteGallery')
            ->willReturnSelf();

        $this->imageProcessor->expects($this->any())
            ->method('removeImage')
            ->willReturnSelf();

        $this->mediaImageDeleteProcessor->execute($this->productMock);
    }

    /**
     * @return array
     */
    public static function executeCategoryProductMediaDeleteDataProvider(): array
    {
        $imageDirectoryPath = '/media/dir1/dir2/catalog/product/';
        $image1FilePath = '/test/test1.jpg';
        $image2FilePath = '/test/test2.jpg';
        $productImages = [
            new DataObject([
                'value_id' => 1,
                'file' => $image1FilePath,
                'media_type' => 'image',
                'path' => $imageDirectoryPath.$image1FilePath
            ]),
            new DataObject([
                'value_id' => 2,
                'file' => $image2FilePath,
                'media_type' => 'image',
                'path' => $imageDirectoryPath.$image2FilePath
            ])
        ];
        return [
            'test image can be deleted with existing product and product images' =>
                [
                    12,
                    $productImages,
                    true,
                    false
                ],
            'test image can not be deleted without valid product id' =>
                [
                    0,
                    $productImages,
                    true,
                    false
                ],
            'test image can not be deleted without valid product images' =>
                [
                    12,
                    [new DataObject(['file' => null]), new DataObject(['file' => null])],
                    true,
                    false
                ],
        ];
    }
}
