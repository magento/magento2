<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\Image\ContextFactory;
use Magento\Catalog\Model\View\Asset\ImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Catalog\Model\Product\Image\SizeCache;

/**
 * Class ImageTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Image
     */
    protected $image;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreFileHelper;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Image\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    /**
     * @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigInterface;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageAsset;

    /**
     * @var ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewAssetImageFactory;

    /**
     * @var PlaceholderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewAssetPlaceholderFactory;

    /**
     * @var ParamsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paramsBuilder;

    /**
     * @var SizeCache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sizeCache;

    protected function setUp()
    {
        $this->context = $this->getMock(\Magento\Framework\Model\Context::class, [], [], '', false);
        $this->registry = $this->getMock(\Magento\Framework\Registry::class);

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getWebsite'])->getMock();
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()
            ->setMethods(['getId', '__sleep', '__wakeup', 'getBaseUrl'])->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://magento.com/media/'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->config = $this->getMockBuilder(\Magento\Catalog\Model\Product\Media\Config::class)
            ->setMethods(['getBaseMediaPath'])->disableOriginalConstructor()->getMock();
        $this->config->expects($this->any())->method('getBaseMediaPath')->will($this->returnValue('catalog/product'));
        $this->coreFileHelper = $this->getMockBuilder(\Magento\MediaStorage\Helper\File\Storage\Database::class)
            ->setMethods(['saveFile', 'deleteFolder'])->disableOriginalConstructor()->getMock();

        $this->mediaDirectory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Write::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'isFile', 'isExist', 'getAbsolutePath'])
            ->getMock();

        $this->filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($this->mediaDirectory));
        $this->factory = $this->getMock(\Magento\Framework\Image\Factory::class, [], [], '', false);
        $this->repository = $this->getMock(\Magento\Framework\View\Asset\Repository::class, [], [], '', false);
        $this->fileSystem = $this->getMock(\Magento\Framework\View\FileSystem::class, [], [], '', false);
        $this->scopeConfigInterface = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $context = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewAssetImageFactory = $this->getMockBuilder(ImageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->viewAssetPlaceholderFactory = $this->getMockBuilder(PlaceholderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->paramsBuilder = $this->getMockBuilder(ParamsBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sizeCache = $this->getMockBuilder(SizeCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->image = new \Magento\Catalog\Model\Product\Image(
            $context,
            $this->registry,
            $this->storeManager,
            $this->config,
            $this->coreFileHelper,
            $this->filesystem,
            $this->factory,
            $this->repository,
            $this->fileSystem,
            $this->scopeConfigInterface,
            null,
            null,
            [],
            $this->viewAssetImageFactory,
            $this->viewAssetPlaceholderFactory,
            $this->paramsBuilder,
            $this->sizeCache
        );

        //Settings for backward compatible property
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->imageAsset = $this->getMockBuilder(\Magento\Framework\View\Asset\LocalInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerHelper->setBackwardCompatibleProperty(
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
        $this->mediaDirectory->expects($this->any())->method('isFile')->will($this->returnValue(true));
        $this->mediaDirectory->expects($this->any())->method('isExist')->will($this->returnValue(true));
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/somefile.png';
        $this->mediaDirectory->expects($this->any())->method('getAbsolutePath')
            ->will($this->returnValue($absolutePath));
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
        $this->assertEquals(
            null,
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
            ->with($this->image->getWidth(), $this->image->getHeight())->will($this->returnValue(true));
        $this->image->setImageProcessor($imageProcessor);
        $result = $this->image->resize();
        $this->assertSame($this->image, $result);
    }

    public function testRotate()
    {
        $imageProcessor = $this->getMockBuilder(\Magento\Framework\Image::class)->disableOriginalConstructor()
            ->getMock();
        $imageProcessor->expects($this->once())->method('rotate')->with(90)->will($this->returnValue(true));
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
        $website->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($website));
        $this->mediaDirectory->expects($this->at(3))->method('isExist')->with('catalog/product/watermark//somefile.png')
            ->will($this->returnValue(true));
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->mediaDirectory->expects($this->any())->method('getAbsolutePath')
            ->with('catalog/product/watermark//somefile.png')
            ->will($this->returnValue($absolutePath));

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
            ->will($this->returnValue(true));
        $imageProcessor->expects($this->once())->method('setWatermarkImageOpacity')->with(50)
            ->will($this->returnValue(true));
        $imageProcessor->expects($this->once())->method('setWatermarkWidth')->with(100)
            ->will($this->returnValue(true));
        $imageProcessor->expects($this->once())->method('setWatermarkHeight')->with(100)
            ->will($this->returnValue(true));
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
        $this->coreFileHelper->expects($this->once())->method('saveFile')->will($this->returnValue(true));
        $this->imageAsset->expects($this->any())
            ->method('getPath')
            ->willReturn('specific_path');
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
        $this->imageAsset->expects($this->any())->method('getUrl')->will($this->returnValue('url of exist image'));
        $this->assertEquals('url of exist image', $this->image->getUrl());
    }

    public function testGetUrlNoSelection()
    {
        $this->viewAssetPlaceholderFactory->expects($this->once())->method('create')->willReturn($this->imageAsset);
        $this->imageAsset->expects($this->any())->method('getUrl')->will($this->returnValue('Default Placeholder URL'));
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
        $this->assertTrue($this->image->isCached());
    }

    public function testClearCache()
    {
        $this->coreFileHelper->expects($this->once())->method('deleteFolder')->will($this->returnValue(true));
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
        $this->factory->expects($this->once())->method('create')->will($this->returnValue($imageProcessor));
        $this->assertSame($imageProcessor, $this->image->getImageProcessor());
    }

    public function testIsBaseFilePlaceholder()
    {
        $this->assertFalse($this->image->isBaseFilePlaceholder());
    }
}
