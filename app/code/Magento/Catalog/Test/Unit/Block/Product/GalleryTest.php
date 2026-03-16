<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Block\Product\Gallery;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * @covers \Magento\Catalog\Block\Product\Gallery
 */
class GalleryTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Gallery
     */
    private Gallery $block;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;
    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequest', 'getFilesystem', 'getUrlBuilder'])
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['registry'])
            ->getMock();
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMediaGalleryImages'])
            ->getMock();
        $this->fileSystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDirectoryRead'])
            ->getMock();
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItemById', 'getFirstItem'])
            ->getMock();

        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->method('getFilesystem')->willReturn($this->fileSystemMock);
        $this->contextMock->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $this->registryMock->method('registry')->with('product')->willReturn($this->productMock);

        $this->block = $this->objectManager->getObject(
            Gallery::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
            ]
        );
    }

    /**
     * Unit test coverage for getImageFile()
     *
     * @return void
     */
    public function testGetImageFileReturnsFileFromCurrentImageWhenImageParamPresent(): void
    {
        $imageId = 123;
        $expectedFile = 'path/to/image.jpg';
        $imageMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getFile']
        );

        $this->requestMock->method('getParam')->with('image')->willReturn($imageId);
        $this->productMock->expects($this->once())->method('getMediaGalleryImages')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getItemById')->with($imageId)->willReturn($imageMock);
        $imageMock->expects($this->once())->method('getFile')->willReturn($expectedFile);

        $this->assertSame($expectedFile, $this->block->getImageFile());
    }

    /**
     * Unit test coverage for getImageFile()
     *
     * @return void
     */
    public function testGetImageFileReturnsFileWhenImageParamIsNotPresent(): void
    {
        $expectedFile = 'path/to/image.jpg';
        $imageMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getFile']
        );

        $this->requestMock->method('getParam')->with('image')->willReturn(null);
        $this->productMock->expects($this->once())->method('getMediaGalleryImages')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getFirstItem')->willReturn($imageMock);
        $imageMock->expects($this->once())->method('getFile')->willReturn($expectedFile);

        $this->assertSame($expectedFile, $this->block->getImageFile());
    }

    /**
     * Consolidated test for getImageWidth() using a data provider to cover multiple scenarios.
     *
     * @param array $fileStat
     * @param bool $isFile
     * @param int|bool $expectedSize
     */
    #[DataProvider('imageWidthProvider')]
    public function testGetImageWidth(array $fileStat, bool $isFile, $expectedSize): void
    {
        $imageId = 123;
        $expectedFile = 'path/to/image.jpg';

        $imageMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getPath']
        );
        $dirReadMock = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['stat', 'isFile'])
            ->getMock();

        $this->requestMock->method('getParam')->with('image')->willReturn($imageId);
        $this->productMock->expects($this->once())->method('getMediaGalleryImages')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getItemById')->with($imageId)->willReturn($imageMock);
        $imageMock->expects($this->once())->method('getPath')->willReturn($expectedFile);
        $this->fileSystemMock->expects($this->exactly(2))->method('getDirectoryRead')->with("media")
            ->willReturn($dirReadMock);
        $dirReadMock->expects($this->once())->method('stat')->willReturn($fileStat);
        $dirReadMock->expects($this->once())->method('isFile')->with($expectedFile)->willReturn($isFile);

        $this->assertSame($expectedSize, $this->block->getImageWidth());
    }

    /**
     * Data provider for testGetImageWidth()
     *
     * @return array
     */
    public static function imageWidthProvider(): array
    {
        return [
            'large-file' => [['size' => [1000]], true, 600],
            'small-file' => [['size' => [100]], true, 100],
            'not-a-file' => [['size' => [100]], false, false],
            'empty-size' => [['size' => []], true, false]
        ];
    }

    /**
     * Unit test coverage for getImageUrl()
     *
     * @return void
     */
    public function testGetImageUrl(): void
    {
        $imageId = 123;
        $imageUrl = 'http://example.com/media/image.jpg';
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItemById', 'getFirstItem'])
            ->getMock();
        $imageMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getUrl']
        );

        $this->requestMock->method('getParam')->with('image')->willReturn($imageId);
        $this->productMock->expects($this->exactly(2))->method('getMediaGalleryImages')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getItemById')->with($imageId)->willReturn(null);
        $collectionMock->expects($this->once())->method('getFirstItem')->willReturn($imageMock);
        $imageMock->expects($this->once())->method('getUrl')->willReturn($imageUrl);

        $this->assertSame($imageUrl, $this->block->getImageUrl());
    }

    /**
     * Unit test coverage for getImageUrl()
     *
     * @return void
     */
    public function testGetImageUrlWithoutImageParam(): void
    {
        $imageUrl = 'http://example.com/media/image.jpg';
        $imageMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getUrl']
        );

        $this->requestMock->method('getParam')->with('image')->willReturn(null);
        $this->productMock->expects($this->once())->method('getMediaGalleryImages')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getFirstItem')->willReturn($imageMock);
        $imageMock->expects($this->once())->method('getUrl')->willReturn($imageUrl);

        $this->assertSame($imageUrl, $this->block->getImageUrl());
    }

    /**
     * Unit test coverage for getPreviousImageUrl()
     * Test getPreviousImageUrl returns last image URL when current not in collection
     *
     * @return void
     */
    public function testGetPreviousImageUrl(): void
    {
        $imageId = 100;
        $expectedImageUrl = 'http://example.com/media/image.jpg';
        $imageMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getValueId']
        );
        $currentImageMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getValueId']
        );

        $this->collectionMock->addItem($imageMock);
        $this->requestMock->method('getParam')
            ->with('image')
            ->willReturn($imageId);
        $this->productMock->expects($this->exactly(2))
            ->method('getMediaGalleryImages')
            ->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())
            ->method('getItemById')->with($imageId)
            ->willReturn($currentImageMock);
        $imageMock->expects($this->exactly(2))
            ->method('getValueId')
            ->willReturn($imageId);
        $currentImageMock->expects($this->once())
            ->method('getValueId')
            ->willReturn(200);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($expectedImageUrl);

        $this->assertSame($expectedImageUrl, $this->block->getPreviousImageUrl());
    }

    /**
     * Unit test coverage for getPreviousImageUrl()
     *
     * @return void
     */
    public function testGetPreviousImageUrlReturnsFalse(): void
    {
        $this->requestMock->method('getParam')->with('image')->willReturn(null);

        $this->productMock->expects($this->once())->method('getMediaGalleryImages')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getFirstItem')->willReturn(null);

        $this->assertFalse($this->block->getPreviousImageUrl());
    }

    /**
     * Unit test coverage for getNextImageUrl()
     *
     * @return void
     */
    public function testGetNextImageUrl(): void
    {
        $this->requestMock->method('getParam')->with('image')->willReturn(100);
        $this->productMock->expects($this->exactly(2))
            ->method('getMediaGalleryImages')
            ->willReturn($this->collectionMock);

        $this->assertFalse($this->block->getNextImageUrl());
    }

    /**
     * Unit test coverage for getNextImageUrl()
     *
     * @return void
     */
    public function testGetNextImageUrlReturnsExpectedImageUrl(): void
    {
        $imageId = 100;
        $expectedImageUrl = 'http://example.com/media/next_image.jpg';

        $imageBefore = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getValueId']
        );
        $imageCurrentInCollection = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getValueId']
        );
        $imageNext = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getValueId']
        );
        $currentImageMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getValueId']
        );

        $this->collectionMock->addItem($imageBefore);
        $this->collectionMock->addItem($imageCurrentInCollection);
        $this->collectionMock->addItem($imageNext);

        $this->requestMock->method('getParam')->with('image')->willReturn($imageId);
        $this->productMock->expects($this->exactly(2))
            ->method('getMediaGalleryImages')
            ->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())
            ->method('getItemById')->with($imageId)
            ->willReturn($currentImageMock);

        $imageBefore->expects($this->once())->method('getValueId')->willReturn(50);
        $imageCurrentInCollection->expects($this->once())->method('getValueId')->willReturn($imageId);
        $imageNext->expects($this->once())->method('getValueId')->willReturn(200);
        $currentImageMock->expects($this->exactly(2))->method('getValueId')->willReturn($imageId);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($expectedImageUrl);

        $this->assertSame($expectedImageUrl, $this->block->getNextImageUrl());
    }

    /**
     * Unit test coverage for getCurrentImage()
     *
     * @return void
     */
    public function testGetCurrentImageReturnsItemByIdWhenParamPresent(): void
    {
        $imageId = 123;
        $imageMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->method('getParam')->with('image')->willReturn($imageId);
        $this->productMock->expects($this->once())->method('getMediaGalleryImages')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getItemById')->with($imageId)->willReturn($imageMock);

        $this->assertSame($imageMock, $this->block->getCurrentImage());
    }

    /**
     * Unit test coverage for getCurrentImage()
     *
     * @return void
     */
    public function testGetCurrentImageReturnsFirstItemWhenItemByIdNotFound(): void
    {
        $imageId = 123;
        $firstImageMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->method('getParam')->with('image')->willReturn($imageId);
        $this->productMock->expects($this->exactly(2))
            ->method('getMediaGalleryImages')
            ->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getItemById')->with($imageId)->willReturn(null);
        $this->collectionMock->expects($this->once())->method('getFirstItem')->willReturn($firstImageMock);

        $this->assertSame($firstImageMock, $this->block->getCurrentImage());
    }

    /**
     * Unit test coverage for getCurrentImage()
     *
     * @return void
     */
    public function testGetCurrentImageReturnsFirstItemWhenNoParam(): void
    {
        $firstImageMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->method('getParam')->with('image')->willReturn(null);
        $this->productMock->expects($this->once())->method('getMediaGalleryImages')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getFirstItem')->willReturn($firstImageMock);

        $this->assertSame($firstImageMock, $this->block->getCurrentImage());
    }
}
