<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\Image\ContextFactory;
use Magento\Catalog\Model\View\Asset\ImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Image
     */
    private $image;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $config;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit\Framework\MockObject\MockObject
     */
    private $coreFileHelper;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Image\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $imageAsset;

    /**
     * @var ImageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $viewAssetImageFactory;

    /**
     * @var PlaceholderFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $viewAssetPlaceholderFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheManager;

    /**
     * @var ParamsBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paramsBuilder;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $this->createMock(\Magento\Framework\Model\Context::class);
        $this->cacheManager = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->any())->method('getCacheManager')->willReturn($this->cacheManager);

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getWebsite'])->getMock();
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()
            ->setMethods(['getId', '__sleep', '__wakeup', 'getBaseUrl'])->getMock();
        $store->expects($this->any())->method('getId')->willReturn(1);
        $store->expects($this->any())->method('getBaseUrl')->willReturn('http://magento.com/media/');
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->config = $this->getMockBuilder(\Magento\Catalog\Model\Product\Media\Config::class)
            ->setMethods(['getBaseMediaPath'])->disableOriginalConstructor()->getMock();
        $this->config->expects($this->any())->method('getBaseMediaPath')->willReturn('catalog/product');
        $this->coreFileHelper = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->setMethods(['saveFile', 'deleteFolder'])->disableOriginalConstructor()->getMock();

        $this->mediaDirectory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Write::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'isFile', 'isExist', 'getAbsolutePath'])
            ->getMock();

        $this->filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);
        $this->factory = $this->createMock(\Magento\Framework\Image\Factory::class);

        $this->viewAssetImageFactory = $this->getMockBuilder(ImageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->viewAssetPlaceholderFactory = $this->getMockBuilder(PlaceholderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->serializer = $this->getMockBuilder(
            \Magento\Framework\Serialize\SerializerInterface::class
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
            \Magento\Catalog\Model\Product\Image::class,
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

        $this->imageAsset = $this->getMockBuilder(\Magento\Framework\View\Asset\LocalInterface::class)
            ->getMockForAbstractClass();
        $objectManager->setBackwardCompatibleProperty(
            $this->image,
            'imageAsset',
            $this->imageAsset
        );
    }

    public function testSetGetQuality()
    {
        $this->image->setQuality(100);
        $this->assertEquals(100, $this->image->getQuality());
    }

    public function testSetGetKeepAspectRatio()
    {
        $result = $this->image->setKeepAspectRatio(true);
        $this->assertSame($this->image, $result);
    }

    public function testSetKeepFrame()
    {
        $result = $this->image->setKeepFrame(true);
        $this->assertSame($this->image, $result);
    }

    public function testSetKeepTransparency()
    {
        $result = $this->image->setKeepTransparency(true);
        $this->assertSame($this->image, $result);
    }

    public function testSetConstrainOnly()
    {
        $result = $this->image->setConstrainOnly(true);
        $this->assertSame($this->image, $result);
    }

    public function testSetBackgroundColor()
    {
        $result = $this->image->setBackgroundColor([0, 0, 0]);
        $this->assertSame($this->image, $result);
    }

    public function testSetSize()
    {
        $this->image->setSize('99xsadf');
        $this->assertEquals(99, $this->image->getWidth());
        $this->assertNull($this->image->getHeight());
    }

    public function testSetGetBaseFile()
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
            'quality' => 80,
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
                    'filePath' => '/somefile.png',
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

    public function testSetBaseNoSelectionFile()
    {
        $this->viewAssetPlaceholderFactory->expects($this->once())->method('create')->willReturn($this->imageAsset);
        $this->imageAsset->expects($this->any())->method('getSourceFile')->willReturn('Default Placeholder Path');
        $this->image->setBaseFile('no_selection');
        $this->assertEquals('Default Placeholder Path', $this->image->getBaseFile());
    }

    public function testSetGetImageProcessor()
    {
        $imageProcessor = $this->getMockBuilder(\Magento\Framework\Image::class)->disableOriginalConstructor()
            ->getMock();
        $result = $this->image->setImageProcessor($imageProcessor);
        $this->assertSame($this->image, $result);
        $this->assertSame($imageProcessor, $this->image->getImageProcessor());
    }

    public function testResize()
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

    public function testRotate()
    {
        $imageProcessor = $this->getMockBuilder(\Magento\Framework\Image::class)->disableOriginalConstructor()
            ->getMock();
        $imageProcessor->expects($this->once())->method('rotate')->with(90)->willReturn(true);
        $this->image->setImageProcessor($imageProcessor);
        $result = $this->image->rotate(90);
        $this->assertSame($this->image, $result);
    }

    public function testSetAngle()
    {
        $result = $this->image->setAngle(90);
        $this->assertSame($this->image, $result);
    }

    public function testSetWatermark()
    {
        $website = $this->getMockBuilder(\Magento\Store\Model\Website::class)->disableOriginalConstructor()
            ->setMethods(['getId', '__sleep', '__wakeup'])->getMock();
        $website->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getWebsite')->willReturn($website);
        $this->mediaDirectory->expects($this->at(3))->method('isExist')->with('catalog/product/watermark//somefile.png')
            ->willReturn(true);
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->mediaDirectory->expects($this->any())->method('getAbsolutePath')
            ->with('catalog/product/watermark//somefile.png')
            ->willReturn($absolutePath);

        $imageProcessor = $this->getMockBuilder(\Magento\Framework\Image::class)->disableOriginalConstructor()
            ->setMethods([
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
                'watermark',
            ])->getMock();
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

    public function testSaveFile()
    {
        $imageProcessor = $this->getMockBuilder(
            \Magento\Framework\Image::class
        )->disableOriginalConstructor()->getMock();
        $this->image->setImageProcessor($imageProcessor);
        $this->coreFileHelper->expects($this->once())->method('saveFile')->willReturn(true);

        $this->image->saveFile();
    }

    public function testSaveFileNoSelection()
    {
        $imageProcessor = $this->getMockBuilder(
            \Magento\Framework\Image::class
        )->disableOriginalConstructor()->getMock();
        $this->image->setImageProcessor($imageProcessor);
        $this->assertSame($this->image, $this->image->saveFile());
    }

    public function testGetUrl()
    {
        $this->testSetGetBaseFile();
        $this->imageAsset->expects($this->any())->method('getUrl')->willReturn('url of exist image');
        $this->assertEquals('url of exist image', $this->image->getUrl());
    }

    public function testGetUrlNoSelection()
    {
        $this->viewAssetPlaceholderFactory->expects($this->once())->method('create')->willReturn($this->imageAsset);
        $this->imageAsset->expects($this->any())->method('getUrl')->willReturn('Default Placeholder URL');
        $this->image->setBaseFile('no_selection');
        $this->assertEquals('Default Placeholder URL', $this->image->getUrl());
    }

    public function testSetGetDestinationSubdir()
    {
        $this->image->setDestinationSubdir('image_type');
        $this->assertEquals('image_type', $this->image->getDestinationSubdir());
    }

    public function testIsCached()
    {
        $this->testSetGetBaseFile();
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->imageAsset->expects($this->any())->method('getPath')->willReturn($absolutePath);
        $this->cacheManager->expects($this->once())->method('load')->willReturn(
            json_encode(['size' => ['image data']])
        );
        $this->assertTrue($this->image->isCached());
    }

    public function testClearCache()
    {
        $this->coreFileHelper->expects($this->once())->method('deleteFolder')->willReturn(true);
        $this->cacheManager->expects($this->once())->method('clean');
        $this->image->clearCache();
    }

    public function testResizeWithoutSize()
    {
        $this->image->setHeight(null);
        $this->image->setWidth(null);
        $this->assertSame($this->image, $this->image->resize());
    }

    public function testGetImageProcessor()
    {
        $imageProcessor = $this->getMockBuilder(
            \Magento\Framework\Image::class
        )->disableOriginalConstructor()->getMock();
        $this->factory->expects($this->once())->method('create')->willReturn($imageProcessor);
        $this->assertSame($imageProcessor, $this->image->getImageProcessor());
    }

    public function testIsBaseFilePlaceholder()
    {
        $this->assertFalse($this->image->isBaseFilePlaceholder());
    }

    public function testGetResizedImageInfoWithCache()
    {
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->imageAsset->expects($this->any())->method('getPath')->willReturn($absolutePath);
        $this->cacheManager->expects($this->once())->method('load')->willReturn(
            json_encode(['size' => ['image data']])
        );
        $this->cacheManager->expects($this->never())->method('save');
        $this->assertEquals(['image data'], $this->image->getResizedImageInfo());
    }

    public function testGetResizedImageInfoEmptyCache()
    {
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->imageAsset->expects($this->any())->method('getPath')->willReturn($absolutePath);
        $this->cacheManager->expects($this->once())->method('load')->willReturn(false);
        $this->cacheManager->expects($this->once())->method('save');
        $this->assertIsArray($this->image->getResizedImageInfo());
    }
}
