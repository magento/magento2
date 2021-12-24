<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product\Image;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\View\Asset\ImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Image\Factory;
use Magento\Framework\Model\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ImageTest extends TestCase
{
    /**
     * @var Image
     */
    private $image;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var Database|MockObject
     */
    private $coreFileHelper;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var Factory|MockObject
     */
    private $factory;

    /**
     * @var WriteInterface|MockObject
     */
    private $mediaDirectory;

    /**
     * @var LocalInterface|MockObject
     */
    private $imageAsset;

    /**
     * @var ImageFactory|MockObject
     */
    private $viewAssetImageFactory;

    /**
     * @var PlaceholderFactory|MockObject
     */
    private $viewAssetPlaceholderFactory;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheManager;

    /**
     * @var ParamsBuilder|MockObject
     */
    private $paramsBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->context = $this->createMock(Context::class);
        $this->cacheManager = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->any())->method('getCacheManager')->willReturn($this->cacheManager);

        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore', 'getWebsite'])->getMock();
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', '__sleep', 'getBaseUrl'])->getMock();
        $store->expects($this->any())->method('getId')->willReturn(1);
        $store->expects($this->any())->method('getBaseUrl')->willReturn('http://magento.com/media/');
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->config = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getBaseMediaPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->any())->method('getBaseMediaPath')->willReturn('catalog/product');
        $this->coreFileHelper = $this->getMockBuilder(Database::class)
            ->onlyMethods(['saveFile', 'deleteFolder'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaDirectory = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create', 'isFile', 'isExist', 'getAbsolutePath', 'isDirectory', 'getDriver', 'delete'])
            ->getMock();

        $this->filesystem = $this->createMock(Filesystem::class);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);
        $this->factory = $this->createMock(Factory::class);

        $this->viewAssetImageFactory = $this->getMockBuilder(ImageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->viewAssetPlaceholderFactory = $this->getMockBuilder(PlaceholderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->serializer = $this->getMockBuilder(
            SerializerInterface::class
        )->getMockForAbstractClass();
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );
        $this->paramsBuilder = $this->getMockBuilder(ParamsBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->image = $objectManager->getObject(
            Image::class,
            [
                'context' => $this->context,
                'storeManager' => $this->storeManager,
                'catalogProductMediaConfig' => $this->config,
                'coreFileStorageDatabase' => $this->coreFileHelper,
                'filesystem' => $this->filesystem,
                'imageFactory' => $this->factory,
                'viewAssetImageFactory' => $this->viewAssetImageFactory,
                'viewAssetPlaceholderFactory' => $this->viewAssetPlaceholderFactory,
                'serializer' => $this->serializer,
                'paramsBuilder' => $this->paramsBuilder
            ]
        );

        $this->imageAsset = $this->getMockBuilder(LocalInterface::class)
            ->getMockForAbstractClass();
        $objectManager->setBackwardCompatibleProperty(
            $this->image,
            'imageAsset',
            $this->imageAsset
        );
    }

    /**
     * @return void
     */
    public function testSetGetQuality(): void
    {
        $this->image->setQuality(100);
        $this->assertEquals(100, $this->image->getQuality());
    }

    /**
     * @return void
     */
    public function testSetGetKeepAspectRatio(): void
    {
        $result = $this->image->setKeepAspectRatio(true);
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testSetKeepFrame(): void
    {
        $result = $this->image->setKeepFrame(true);
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testSetKeepTransparency(): void
    {
        $result = $this->image->setKeepTransparency(true);
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testSetConstrainOnly(): void
    {
        $result = $this->image->setConstrainOnly(true);
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testSetBackgroundColor(): void
    {
        $result = $this->image->setBackgroundColor([0, 0, 0]);
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testSetSize(): void
    {
        $this->image->setSize('99xsadf');
        $this->assertEquals(99, $this->image->getWidth());
        $this->assertNull($this->image->getHeight());
    }

    /**
     * @return void
     */
    public function testSetGetBaseFile(): void
    {
        $miscParams = [
            'image_type' => null,
            'image_height' => null,
            'image_width' => null,
            'keep_aspect_ratio' => 'proportional',
            'keep_frame' => 'frame',
            'keep_transparency' => 'transparency',
            'constrain_only' => 'doconstrainonly',
            'background' => 'ffffff',
            'angle' => null,
            'quality' => 80
        ];
        $this->paramsBuilder->expects(self::once())
            ->method('build')
            ->willReturn($miscParams);
        $this->mediaDirectory->expects($this->any())->method('isFile')->willReturn(true);
        $this->mediaDirectory->expects($this->any())->method('isExist')->willReturn(true);
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/somefile.png';
        $this->mediaDirectory->expects($this->any())->method('getAbsolutePath')
            ->willReturn($absolutePath);
        $this->viewAssetImageFactory->expects($this->any())
            ->method('create')
            ->with(
                [
                    'miscParams' => $miscParams,
                    'filePath' => '/somefile.png'
                ]
            )
            ->willReturn($this->imageAsset);
        $this->viewAssetPlaceholderFactory->expects($this->never())->method('create');

        $this->imageAsset->expects($this->any())->method('getSourceFile')->willReturn('catalog/product/somefile.png');
        $this->image->setBaseFile('/somefile.png');
        $this->assertEquals('catalog/product/somefile.png', $this->image->getBaseFile());
        $this->assertNull(
            $this->image->getNewFile()
        );
    }

    /**
     * @return void
     */
    public function testSetBaseNoSelectionFile(): void
    {
        $this->viewAssetPlaceholderFactory->expects($this->once())->method('create')->willReturn($this->imageAsset);
        $this->imageAsset->expects($this->any())->method('getSourceFile')->willReturn('Default Placeholder Path');
        $this->image->setBaseFile('no_selection');
        $this->assertEquals('Default Placeholder Path', $this->image->getBaseFile());
    }

    /**
     * @return void
     */
    public function testSetGetImageProcessor(): void
    {
        $imageProcessor = $this->getMockBuilder(\Magento\Framework\Image::class)->disableOriginalConstructor()
            ->getMock();
        $result = $this->image->setImageProcessor($imageProcessor);
        $this->assertSame($this->image, $result);
        $this->assertSame($imageProcessor, $this->image->getImageProcessor());
    }

    /**
     * @return void
     */
    public function testResize(): void
    {
        $this->image->setWidth(100);
        $this->image->setHeight(100);
        $imageProcessor = $this->getMockBuilder(\Magento\Framework\Image::class)->disableOriginalConstructor()
            ->getMock();
        $imageProcessor->expects($this->once())->method('resize')
            ->with($this->image->getWidth(), $this->image->getHeight())->willReturn(true);
        $this->image->setImageProcessor($imageProcessor);
        $result = $this->image->resize();
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testRotate(): void
    {
        $imageProcessor = $this->getMockBuilder(\Magento\Framework\Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageProcessor->expects($this->once())->method('rotate')->with(90)->willReturn(true);
        $this->image->setImageProcessor($imageProcessor);
        $result = $this->image->rotate(90);
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testSetAngle(): void
    {
        $result = $this->image->setAngle(90);
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testSetWatermark(): void
    {
        $website = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', '__sleep'])->getMock();
        $website->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getWebsite')->willReturn($website);
        $this->mediaDirectory
            ->method('isExist')
            ->withConsecutive([], [], [], ['catalog/product/watermark//somefile.png'])
            ->willReturnOnConsecutiveCalls(null, null, null, true);
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->mediaDirectory->expects($this->any())->method('getAbsolutePath')
            ->with('catalog/product/watermark//somefile.png')
            ->willReturn($absolutePath);

        $imageProcessor = $this->getMockBuilder(\Magento\Framework\Image::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'keepAspectRatio',
                    'keepFrame',
                    'keepTransparency',
                    'constrainOnly',
                    'backgroundColor',
                    'quality',
                    'setWatermarkPosition',
                    'setWatermarkImageOpacity',
                    'setWatermarkWidth',
                    'setWatermarkHeight',
                    'watermark'
                ]
            )->getMock();
        $imageProcessor->expects($this->once())->method('setWatermarkPosition')->with('center')
            ->willReturn(true);
        $imageProcessor->expects($this->once())->method('setWatermarkImageOpacity')->with(50)
            ->willReturn(true);
        $imageProcessor->expects($this->once())->method('setWatermarkWidth')->with(100)
            ->willReturn(true);
        $imageProcessor->expects($this->once())->method('setWatermarkHeight')->with(100)
            ->willReturn(true);
        $this->image->setImageProcessor($imageProcessor);

        $result = $this->image->setWatermark(
            '/somefile.png',
            'center',
            ['width' => 100, 'height' => 100],
            100,
            100,
            50
        );
        $this->assertSame($this->image, $result);
    }

    /**
     * @return void
     */
    public function testSaveFile(): void
    {
        $imageProcessor = $this->getMockBuilder(
            \Magento\Framework\Image::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->image->setImageProcessor($imageProcessor);
        $this->coreFileHelper->expects($this->once())->method('saveFile')->willReturn(true);

        $this->image->saveFile();
    }

    /**
     * @return void
     */
    public function testSaveFileNoSelection(): void
    {
        $imageProcessor = $this->getMockBuilder(
            \Magento\Framework\Image::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->image->setImageProcessor($imageProcessor);
        $this->assertSame($this->image, $this->image->saveFile());
    }

    /**
     * @return void
     */
    public function testGetUrl(): void
    {
        $this->testSetGetBaseFile();
        $this->imageAsset->expects($this->any())->method('getUrl')->willReturn('url of exist image');
        $this->assertEquals('url of exist image', $this->image->getUrl());
    }

    /**
     * @return void
     */
    public function testGetUrlNoSelection(): void
    {
        $this->viewAssetPlaceholderFactory->expects($this->once())->method('create')->willReturn($this->imageAsset);
        $this->imageAsset->expects($this->any())->method('getUrl')->willReturn('Default Placeholder URL');
        $this->image->setBaseFile('no_selection');
        $this->assertEquals('Default Placeholder URL', $this->image->getUrl());
    }

    /**
     * @return void
     */
    public function testSetGetDestinationSubdir(): void
    {
        $this->image->setDestinationSubdir('image_type');
        $this->assertEquals('image_type', $this->image->getDestinationSubdir());
    }

    /**
     * @return void
     */
    public function testIsCached(): void
    {
        $this->testSetGetBaseFile();
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->imageAsset->expects($this->any())->method('getPath')->willReturn($absolutePath);
        $this->cacheManager->expects($this->once())->method('load')->willReturn(
            json_encode(['size' => ['image data']])
        );
        $this->assertTrue($this->image->isCached());
    }

    /**
     * @param bool $isRenameSuccessful
     * @param string $expectedDirectoryToDelete
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @dataProvider clearCacheDataProvider
     */
    public function testClearCache(
        bool $isRenameSuccessful,
        string $expectedDirectoryToDelete
    ): void {
        $driver = $this->createMock(DriverInterface::class);
        $this->mediaDirectory->method('getAbsolutePath')
            ->willReturnCallback(
                function (string $path) {
                    return 'path/to/media/' . $path;
                }
            );
        $this->mediaDirectory->expects($this->exactly(2))
            ->method('isDirectory')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->mediaDirectory->expects($this->once())
            ->method('getDriver')
            ->willReturn($driver);
        $driver->expects($this->once())
            ->method('rename')
            ->with(
                'path/to/media/catalog/product/cache',
                $this->matchesRegularExpression('/^path\/to\/media\/catalog\/product\/\.[0-9A-ZA-z-_]{3}$/')
            )
            ->willReturn($isRenameSuccessful);
        $this->mediaDirectory->expects($this->once())
            ->method('delete')
            ->with($this->matchesRegularExpression($expectedDirectoryToDelete));

        $this->coreFileHelper->expects($this->once())->method('deleteFolder')->willReturn(true);
        $this->cacheManager->expects($this->once())->method('clean');
        $this->image->clearCache();
    }

    /**
     * @return array
     */
    public function clearCacheDataProvider(): array
    {
        return [
            [true, '/^catalog\/product\/\.[0-9A-ZA-z-_]{3}$/'],
            [false, '/^catalog\/product\/cache$/'],
        ];
    }

    /**
     * @return void
     */
    public function testResizeWithoutSize(): void
    {
        $this->image->setHeight(null);
        $this->image->setWidth(null);
        $this->assertSame($this->image, $this->image->resize());
    }

    /**
     * @return void
     */
    public function testGetImageProcessor(): void
    {
        $imageProcessor = $this->getMockBuilder(
            \Magento\Framework\Image::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->factory->expects($this->once())->method('create')->willReturn($imageProcessor);
        $this->assertSame($imageProcessor, $this->image->getImageProcessor());
    }

    /**
     * @return void
     */
    public function testIsBaseFilePlaceholder(): void
    {
        $this->assertFalse($this->image->isBaseFilePlaceholder());
    }

    /**
     * @return void
     */
    public function testGetResizedImageInfoWithCache(): void
    {
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->imageAsset->expects($this->any())->method('getPath')->willReturn($absolutePath);
        $this->cacheManager->expects($this->once())->method('load')->willReturn(
            json_encode(['size' => ['image data']])
        );
        $this->cacheManager->expects($this->never())->method('save');
        $this->assertEquals(['image data'], $this->image->getResizedImageInfo());
    }

    /**
     * @return void
     */
    public function testGetResizedImageInfoEmptyCache(): void
    {
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->imageAsset->expects($this->any())->method('getPath')->willReturn($absolutePath);
        $this->cacheManager->expects($this->once())->method('load')->willReturn(false);
        $this->cacheManager->expects($this->once())->method('save');
        $this->assertIsArray($this->image->getResizedImageInfo());
    }
}
