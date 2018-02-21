<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ImageTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    protected function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\Model\Context', [], [], '', false);
        $this->registry = $this->getMock('Magento\Framework\Registry');

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getWebsite'])->getMock();
        $store = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()
            ->setMethods(['getId', '__sleep', '__wakeup', 'getBaseUrl'])->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://magento.com/media/'));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->config = $this->getMockBuilder('Magento\Catalog\Model\Product\Media\Config')
            ->setMethods(['getBaseMediaPath'])->disableOriginalConstructor()->getMock();
        $this->config->expects($this->any())->method('getBaseMediaPath')->will($this->returnValue('catalog/product'));
        $this->coreFileHelper = $this->getMockBuilder('Magento\MediaStorage\Helper\File\Storage\Database')
            ->setMethods(['saveFile', 'deleteFolder'])->disableOriginalConstructor()->getMock();

        $this->mediaDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\Write')
            ->disableOriginalConstructor()
            ->setMethods(['create', 'isFile', 'isExist', 'getAbsolutePath'])
            ->getMock();
        $this->mediaDirectory->expects($this->once())->method('create')->will($this->returnValue(true));

        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($this->mediaDirectory));
        $this->factory = $this->getMock('Magento\Framework\Image\Factory', [], [], '', false);
        $this->repository = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->fileSystem = $this->getMock('Magento\Framework\View\FileSystem', [], [], '', false);
        $this->scopeConfigInterface = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->image = $objectManagerHelper->getObject(
            'Magento\Catalog\Model\Product\Image',
            [
                'registry' => $this->registry,
                'storeManager' => $this->storeManager,
                'catalogProductMediaConfig' => $this->config,
                'coreFileStorageDatabase' => $this->coreFileHelper,
                'filesystem' => $this->filesystem,
                'imageFactory' => $this->factory,
                'assetRepo' => $this->repository,
                'viewFileSystem' => $this->fileSystem,
                'scopeConfig' => $this->scopeConfigInterface
            ]
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
        $this->mediaDirectory->expects($this->any())->method('isFile')->will($this->returnValue(true));
        $this->mediaDirectory->expects($this->any())->method('isExist')->will($this->returnValue(true));
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/somefile.png';
        $this->mediaDirectory->expects($this->any())->method('getAbsolutePath')
            ->will($this->returnValue($absolutePath));
        $this->image->setBaseFile('/somefile.png');
        $this->assertEquals('catalog/product/somefile.png', $this->image->getBaseFile());
        $this->assertEquals(
            'catalog/product/cache//beff4985b56e3afdbeabfc89641a4582/somefile.png',
            $this->image->getNewFile()
        );
    }

    public function testSetBaseNoSelectionFile()
    {
        $this->image->setBaseFile('/no_selection');
        $this->assertTrue($this->image->getNewFile());
    }

    public function testSetGetImageProcessor()
    {
        $imageProcessor = $this->getMockBuilder('Magento\Framework\Image')->disableOriginalConstructor()
            ->getMock();
        $result = $this->image->setImageProcessor($imageProcessor);
        $this->assertSame($this->image, $result);
        $this->assertSame($imageProcessor, $this->image->getImageProcessor());
    }

    public function testResize()
    {
        $this->image->setWidth(100);
        $this->image->setHeight(100);
        $imageProcessor = $this->getMockBuilder('Magento\Framework\Image')->disableOriginalConstructor()
            ->getMock();
        $imageProcessor->expects($this->once())->method('resize')
            ->with($this->image->getWidth(), $this->image->getHeight())->will($this->returnValue(true));
        $this->image->setImageProcessor($imageProcessor);
        $result = $this->image->resize();
        $this->assertSame($this->image, $result);
    }

    public function testRotate()
    {
        $imageProcessor = $this->getMockBuilder('Magento\Framework\Image')->disableOriginalConstructor()
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
        $website = $this->getMockBuilder('\Magento\Store\Model\Website')->disableOriginalConstructor()
            ->setMethods(['getId', '__sleep', '__wakeup'])->getMock();
        $website->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($website));
        $this->mediaDirectory->expects($this->at(3))->method('isExist')->with('catalog/product/watermark//somefile.png')
            ->will($this->returnValue(true));
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/watermark/somefile.png';
        $this->mediaDirectory->expects($this->any())->method('getAbsolutePath')
            ->with('catalog/product/watermark//somefile.png')
            ->will($this->returnValue($absolutePath));

        $imageProcessor = $this->getMockBuilder('Magento\Framework\Image')->disableOriginalConstructor()
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
        $imageProcessor = $this->getMockBuilder('Magento\Framework\Image')->disableOriginalConstructor()->getMock();
        $this->image->setImageProcessor($imageProcessor);
        $this->coreFileHelper->expects($this->once())->method('saveFile')->will($this->returnValue(true));
        $absolutePath = dirname(dirname(__DIR__)) . '/_files/catalog/product/somefile.png';
        $this->mediaDirectory->expects($this->once())->method('getAbsolutePath')
            ->will($this->returnValue($absolutePath));

        $this->image->saveFile();
    }

    public function testSaveFileNoSelection()
    {
        $this->testSetBaseNoSelectionFile();
        $this->assertSame($this->image, $this->image->saveFile());
    }

    public function testGetUrl()
    {
        $this->testSetGetBaseFile();
        $url = $this->image->getUrl();
        $this->assertEquals(
            'http://magento.com/media/catalog/product/cache//beff4985b56e3afdbeabfc89641a4582/somefile.png',
            $url
        );
    }

    public function testGetUrlNoSelection()
    {
        $this->testSetBaseNoSelectionFile();
        $this->repository->expects($this->once())->method('getUrl')->will($this->returnValue('someurl'));
        $this->assertEquals('someurl', $this->image->getUrl());
    }

    public function testSetGetDestinationSubdir()
    {
        $this->image->setDestinationSubdir('somesubdir');
        $this->assertEquals('somesubdir', $this->image->getDestinationSubdir());
    }

    public function testIsCached()
    {
        $this->testSetGetBaseFile();
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
        $imageProcessor = $this->getMockBuilder('\Magento\Framework\Image')->disableOriginalConstructor()->getMock();
        $this->factory->expects($this->once())->method('create')->will($this->returnValue($imageProcessor));
        $this->assertSame($imageProcessor, $this->image->getImageProcessor());
    }

    public function testIsBaseFilePlaceholder()
    {
        $this->assertFalse($this->image->isBaseFilePlaceholder());
    }
}
