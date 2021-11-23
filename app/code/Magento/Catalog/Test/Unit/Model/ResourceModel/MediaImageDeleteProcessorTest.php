<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\MediaImageDeleteProcessor;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Catalog\Model\ResourceModel\MediaImageDeleteProcessor
 */
class MediaImageDeleteProcessorTest extends TestCase
{
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

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getMediaGalleryImages'])
            ->getMock();

        $this->imageConfig = $this->getMockBuilder(MediaConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseMediaUrl', 'getMediaUrl', 'getBaseMediaPath', 'getMediaPath'])
            ->getMock();

        $this->mediaDirectory = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelativePath', 'isFile', 'delete'])
            ->getMock();

        $this->imageProcessor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->setMethods(['removeImage'])
            ->getMock();

        $this->productGallery = $this->getMockBuilder(Gallery::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteGallery', 'countImageUses'])
            ->getMock();

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
     * @dataProvider executeCategoryProductMediaDeleteDataProvider
     * @param int $productId
     * @param array $productImages
     * @param bool $isValidFile
     * @param bool $imageUsedBefore
     */
    public function testExecuteCategoryProductMediaDelete(
        int $productId,
        array $productImages,
        bool $isValidFile,
        bool $imageUsedBefore
    ): void {
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($productId);

        $this->productMock->expects($this->any())
            ->method('getMediaGalleryImages')
            ->willReturn($productImages);

        $this->mediaDirectory->expects($this->any())
            ->method('isFile')
            ->willReturn($isValidFile);

        $this->mediaDirectory->expects($this->any())
            ->method('getRelativePath')
            ->withConsecutive([$productImages[0]->getFile()], [$productImages[1]->getFile()])
            ->willReturnOnConsecutiveCalls($productImages[0]->getPath(), $productImages[1]->getPath());

        $this->productGallery->expects($this->any())
            ->method('countImageUses')
            ->willReturn($imageUsedBefore);

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
    public function executeCategoryProductMediaDeleteDataProvider(): array
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
