<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Service;

use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Catalog\Model\ResourceModel\Product\Image as ProductImage;
use Magento\Catalog\Model\View\Asset\Image as AssetImage;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Config\View;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Framework\View\ConfigInterface as ViewConfig;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Service\ImageResize;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config\Customization as ThemeCustomizationConfig;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageResizeTest extends TestCase
{
    /**
     * @var ImageResize
     */
    private $service;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var MediaConfig|MockObject
     */
    private $imageConfigMock;

    /**
     * @var ProductImage|MockObject
     */
    private $productImageMock;

    /**
     * @var ImageFactory|MockObject
     */
    private $imageFactoryMock;

    /**
     * @var ParamsBuilder|MockObject
     */
    private $paramsBuilderMock;

    /**
     * @var ViewConfig|MockObject
     */
    private $viewConfigMock;

    /**
     * @var View|MockObject
     */
    private $viewMock;

    /**
     * @var AssetImage|MockObject
     */
    private $assetImageMock;

    /**
     * @var AssetImageFactory|MockObject
     */
    private $assetImageFactoryMock;

    /**
     * @var ThemeCustomizationConfig|MockObject
     */
    private $themeCustomizationConfigMock;

    /**
     * @var Collection|MockObject
     */
    private $themeCollectionMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var Database|MockObject
     */
    private $databaseMock;

    /**
     * @var WriteInterface|MockObject
     */
    private $mediaDirectoryMock;

    /**
     * @var string
     */
    private $testfilename;

    /**
     * @var string
     */
    private $testfilepath;

    /**
     * @var string
     */
    private $testImageHiddenFilename;

    /**
     * @var MockObject|StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $testImageHiddenfilepath;

    protected function setUp(): void
    {
        $this->testfilename = "image.jpg";
        $this->testImageHiddenFilename = "image_hidden.jpg";
        $this->testfilepath = "/image.jpg";
        $this->testImageHiddenfilepath = "/image_hidden.jpg";

        $this->appStateMock = $this->createMock(State::class);
        $this->imageConfigMock = $this->createMock(MediaConfig::class);
        $this->productImageMock = $this->createMock(ProductImage::class);
        $this->imageFactoryMock = $this->createMock(ImageFactory::class);
        $this->paramsBuilderMock = $this->createMock(ParamsBuilder::class);
        $this->viewMock = $this->createMock(View::class);
        $this->viewConfigMock = $this->createMock(ViewConfig::class);
        $this->assetImageMock = $this->createMock(AssetImage::class);
        $this->assetImageFactoryMock = $this->createMock(AssetImageFactory::class);
        $this->themeCustomizationConfigMock = $this->createMock(ThemeCustomizationConfig::class);
        $this->themeCollectionMock = $this->createMock(Collection::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->databaseMock = $this->createMock(Database::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->mediaDirectoryMock = $this->createMock(WriteInterface::class);

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->assetImageMock->expects($this->any())
            ->method('getPath')
            ->willReturnOnConsecutiveCalls($this->testfilepath, $this->testImageHiddenfilepath);
        $this->assetImageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->assetImageMock);

        $this->paramsBuilderMock->expects($this->any())
            ->method('build')
            ->willReturn(
                [
                    'keep_aspect_ratio' => null,
                    'keep_frame' => null,
                    'keep_transparency' => null,
                    'constrain_only' => null,
                    'background' => null,
                    'quality' => null,
                    'image_width' => null,
                    'image_height' => null
                ]
            );

        $this->imageConfigMock->expects($this->any())
            ->method('getMediaPath')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$this->testfilename] => $this->testfilepath,
                [$this->testImageHiddenFilename] => $this->testImageHiddenfilepath
            });

        $this->mediaDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$this->testfilepath] => $this->testfilepath,
                [$this->testImageHiddenfilepath] => $this->testImageHiddenfilepath
            });
        $this->mediaDirectoryMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturnOnConsecutiveCalls($this->testfilepath, $this->testImageHiddenfilepath);

        $this->viewMock->expects($this->any())
            ->method('getMediaEntities')
            ->willReturn(['product_small_image' => ['type' => 'small_image', 'width' => 75, 'height' => 75]]);
        $this->viewConfigMock->expects($this->any())->method('getViewConfig')->willReturn($this->viewMock);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $store->method('getWebsiteId')->willReturn(2);
        $this->storeManager->method('getStores')->willReturn([$store]);

        $this->service = new ImageResize(
            $this->appStateMock,
            $this->imageConfigMock,
            $this->productImageMock,
            $this->imageFactoryMock,
            $this->paramsBuilderMock,
            $this->viewConfigMock,
            $this->assetImageFactoryMock,
            $this->themeCustomizationConfigMock,
            $this->themeCollectionMock,
            $this->filesystemMock,
            $this->databaseMock,
            $this->storeManager
        );
    }

    protected function tearDown(): void
    {
        unset($this->service);
    }

    public function testResizeFromThemesMediaStorageDatabase()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(true);
        $this->databaseMock->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        $imageMock = $this->createMock(Image::class);
        $this->imageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($imageMock);

        $this->productImageMock->expects($this->never())
            ->method('getCountAllProductImages');
        $this->productImageMock->expects($this->never())
            ->method('getAllProductImages');
        $this->productImageMock->expects($this->once())
            ->method('getCountUsedProductImages')
            ->willReturn(1);
        $this->productImageMock->expects($this->once())
            ->method('getUsedProductImages')
            ->willReturnCallback(
                fn () => yield ['filepath' => $this->testfilename, 'website_ids' => '1,2']
            );

        $this->mediaDirectoryMock->expects($this->once())
            ->method('isFile')
            ->willReturnCallback(
                fn ($arg1) => $arg1 === $this->testfilepath
            );
        $this->databaseMock->expects($this->once())
            ->method('saveFileToFilesystem')
            ->willReturnCallback(
                fn ($arg1) => $arg1 === $this->testfilepath ? 1 : false
            );
        $this->databaseMock->expects($this->once())
            ->method('saveFile');

        $generator = $this->service->resizeFromThemes(['test-theme'], true);
        while ($generator->valid()) {
            $resizeInfo = $generator->key();
            $this->assertEquals('image.jpg', $resizeInfo['filename']);
            $this->assertEmpty($resizeInfo['error'], $resizeInfo['error']);
            $generator->next();
        }
    }

    public function testResizeFromThemesImageNotAssignedToWebsite()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(false);

        $this->productImageMock->expects($this->once())
            ->method('getCountUsedProductImages')
            ->willReturn(1);
        $this->productImageMock->expects($this->once())
            ->method('getUsedProductImages')
            ->willReturnCallback(
                fn () => yield ['filepath' => $this->testfilename, 'website_ids' => '1,3']
            );

        $this->mediaDirectoryMock->expects($this->atLeastOnce())
            ->method('isFile')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->imageFactoryMock->expects($this->never())
            ->method('create');

        $generator = $this->service->resizeFromThemes(['test-theme'], true);
        while ($generator->valid()) {
            $resizeInfo = $generator->key();
            $this->assertEquals('image.jpg', $resizeInfo['filename']);
            $this->assertEmpty($resizeInfo['error'], $resizeInfo['error']);
            $generator->next();
        }
    }

    public function testResizeFromThemesHiddenImagesMediaStorageDatabase()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(true);
        $this->databaseMock->expects($this->exactly(2))
            ->method('fileExists')
            ->willReturn(false);

        $imageMock = $this->createMock(Image::class);
        $this->imageFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($imageMock);

        $this->productImageMock->expects($this->never())
            ->method('getCountUsedProductImages');
        $this->productImageMock->expects($this->never())
            ->method('getUsedProductImages');
        $this->productImageMock->expects($this->once())
            ->method('getCountAllProductImages')
            ->willReturn(2);
        $this->productImageMock->expects($this->once())
            ->method('getAllProductImages')
            ->willReturnCallback(
                function () {
                    $data = [[ 'filepath' => $this->testfilename ], [ 'filepath' => $this->testImageHiddenFilename ]];
                    foreach ($data as $e) {
                        yield $e;
                    }
                }
            );

        $this->mediaDirectoryMock->expects($this->exactly(2))
            ->method('isFile')
            ->willReturnCallback(
                fn ($arg1) => in_array($arg1, [$this->testfilepath, $this->testImageHiddenfilepath])
            );
        $this->databaseMock->expects($this->exactly(2))
            ->method('saveFileToFilesystem')
            ->willReturnCallback(
                fn ($arg1) => in_array($arg1, [$this->testfilepath, $this->testImageHiddenfilepath]) ? 1 : false
            );
        $this->databaseMock->expects($this->exactly(2))
            ->method('saveFile');

        $generator = $this->service->resizeFromThemes(['test-theme']);
        while ($generator->valid()) {
            $resizeInfo = $generator->key();
            $this->assertContains($resizeInfo['filename'], [$this->testfilename, $this->testImageHiddenFilename]);
            $this->assertEmpty($resizeInfo['error']);
            $generator->next();
        }
    }

    public function testResizeFromThemesUnsupportedImage()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(true);
        $this->databaseMock->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        $this->imageFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \InvalidArgumentException('Unsupported image format.'));

        $this->productImageMock->expects($this->once())
            ->method('getCountUsedProductImages')
            ->willReturn(1);
        $this->productImageMock->expects($this->once())
            ->method('getUsedProductImages')
            ->willReturnCallback(
                fn () => yield ['filepath' => $this->testfilename, 'website_ids' => '1,2']
            );

        $this->mediaDirectoryMock->expects($this->any())
            ->method('isFile')
            ->willReturnCallback(
                fn ($arg1) => in_array($arg1, [$this->testfilepath, $this->testImageHiddenfilepath])
            );

        $generator = $this->service->resizeFromThemes(['test-theme'], true);
        while ($generator->valid()) {
            $resizeInfo = $generator->key();
            $this->assertEquals('Unsupported image format.', $resizeInfo['error']);
            $generator->next();
        }
    }

    public function testResizeFromImageNameMediaStorageDatabase()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(true);
        $this->databaseMock->expects($this->once())
            ->method('fileExists')
            ->willReturn(false);

        $imageMock = $this->createMock(Image::class);
        $this->imageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($imageMock);

        $this->mediaDirectoryMock->expects($this->exactly(2))
            ->method('isFile')
            ->with($this->testfilepath)
            ->willReturnOnConsecutiveCalls(false, true);

        $this->themeCollectionMock->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn(
                [ new DataObject(['id' => '0']) ]
            );
        $this->themeCustomizationConfigMock->expects($this->once())
            ->method('getStoresByThemes')
            ->willReturn(
                ['0' => []]
            );

        $this->databaseMock->expects($this->once())
            ->method('saveFileToFilesystem')
            ->with($this->testfilepath);
        $this->databaseMock->expects($this->once())
            ->method('saveFile')
            ->with($this->testfilepath);

        $this->service->resizeFromImageName($this->testfilename);
    }

    public function testResizeFromImageNameWithAssignedWebsite()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(false);
        $this->mediaDirectoryMock->expects($this->exactly(2))
            ->method('isFile')
            ->with($this->testfilepath)
            ->willReturnOnConsecutiveCalls(true, false);
        $this->themeCollectionMock->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn(
                [ new DataObject(['id' => '0']) ]
            );
        $this->themeCustomizationConfigMock->expects($this->once())
            ->method('getStoresByThemes')
            ->willReturn(
                ['0' => []]
            );
        $this->productImageMock->expects($this->once())->method('getRelatedWebsiteIds')->willReturn([2]);
        $imageMock = $this->createMock(Image::class);
        $this->imageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($imageMock);

        $this->service->resizeFromImageName($this->testfilename, true);
    }

    public function testResizeFromImageNameWithNotAssignedWebsite()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(false);
        $this->mediaDirectoryMock->expects($this->once())
            ->method('isFile')
            ->with($this->testfilepath)
            ->willReturn(true);
        $this->themeCollectionMock->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn(
                [ new DataObject(['id' => '0']) ]
            );
        $this->themeCustomizationConfigMock->expects($this->once())
            ->method('getStoresByThemes')
            ->willReturn(
                ['0' => []]
            );
        $this->productImageMock->expects($this->once())->method('getRelatedWebsiteIds')->willReturn([3]);
        $this->imageFactoryMock->expects($this->never())
            ->method('create');

        $this->service->resizeFromImageName($this->testfilename, true);
    }

    public function testSkipResizingAlreadyResizedImageOnDisk()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(false);

        $this->mediaDirectoryMock->expects($this->exactly(2))
            ->method('isFile')
            ->willReturn(true);

        $this->themeCollectionMock->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn(
                [ new DataObject(['id' => '0']) ]
            );
        $this->themeCustomizationConfigMock->expects($this->once())
            ->method('getStoresByThemes')
            ->willReturn(
                ['0' => []]
            );

        $this->imageFactoryMock->expects($this->never())
            ->method('create');

        $this->service->resizeFromImageName($this->testfilename);
    }

    public function testSkipResizingAlreadyResizedImageInDatabase()
    {
        $this->databaseMock->expects($this->atLeastOnce())
            ->method('checkDbUsage')
            ->willReturn(true);
        $this->databaseMock->expects($this->once())
            ->method('fileExists')
            ->willReturn(true);

        $this->mediaDirectoryMock->expects($this->exactly(2))
            ->method('isFile')
            ->with($this->testfilepath)
            ->willReturnOnConsecutiveCalls(false, true);

        $this->themeCollectionMock->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn(
                [ new DataObject(['id' => '0']) ]
            );
        $this->themeCustomizationConfigMock->expects($this->once())
            ->method('getStoresByThemes')
            ->willReturn(
                ['0' => []]
            );

        $this->databaseMock->expects($this->never())
            ->method('saveFile');

        $this->service->resizeFromImageName($this->testfilename);
    }
}
