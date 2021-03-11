<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Service;

use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\View\Asset\Image as AssetImage;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Framework\Image;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Framework\App\State;
use Magento\Framework\View\ConfigInterface as ViewConfig;
use Magento\Framework\Config\View;
use Magento\Catalog\Model\ResourceModel\Product\Image as ProductImage;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config\Customization as ThemeCustomizationConfig;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ImageResizeTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageResizeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\MediaStorage\Service\ImageResize
     */
    protected $service;

    /**
     * @var State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $appStateMock;

    /**
     * @var MediaConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $imageConfigMock;

    /**
     * @var ProductImage|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productImageMock;

    /**
     * @var ImageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $imageFactoryMock;

    /**
     * @var Image|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $imageMock;

    /**
     * @var ParamsBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paramsBuilderMock;

    /**
     * @var ViewConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewConfigMock;

    /**
     * @var View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var AssetImage|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $assetImageMock;

    /**
     * @var AssetImageFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $assetImageFactoryMock;

    /**
     * @var ThemeCustomizationConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeCustomizationConfigMock;

    /**
     * @var Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filesystemMock;

    /**
     * @var Database|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $databaseMock;

    /**
     * @var Filesystem|\PHPUnit\Framework\MockObject\MockObject
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
     * @var \PHPUnit\Framework\MockObject\MockObject|StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->testfilename = "image.jpg";
        $this->testfilepath = "/image.jpg";

        $this->appStateMock = $this->createMock(State::class);
        $this->imageConfigMock = $this->createMock(MediaConfig::class);
        $this->productImageMock = $this->createMock(ProductImage::class);
        $this->imageMock = $this->createMock(Image::class);
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
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->mediaDirectoryMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAbsolutePath','isFile','getRelativePath'])
            ->getMock();

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->imageFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->imageMock);
        $this->assetImageMock->expects($this->any())
            ->method('getPath')
            ->willReturn($this->testfilepath);
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
            ->with($this->testfilename)
            ->willReturn($this->testfilepath);
        $this->mediaDirectoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->with($this->testfilepath)
            ->willReturn($this->testfilepath);
        $this->mediaDirectoryMock->expects($this->any())
            ->method('getRelativePath')
            ->with($this->testfilepath)
            ->willReturn($this->testfilepath);

        $this->viewMock->expects($this->any())
            ->method('getMediaEntities')
            ->willReturn(
                ['product_small_image' =>
                    [
                        'type' => 'small_image',
                        'width' => 75,
                        'height' => 75
                    ]
                ]
            );
        $this->viewConfigMock->expects($this->any())
            ->method('getViewConfig')
            ->willReturn($this->viewMock);

        $store = $this->getMockForAbstractClass(\Magento\Store\Api\Data\StoreInterface::class);
        $store
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->storeManager
            ->expects($this->any())
            ->method('getStores')
            ->willReturn([$store]);

        $this->service = new \Magento\MediaStorage\Service\ImageResize(
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
        $this->databaseMock->expects($this->any())
            ->method('checkDbUsage')
            ->willReturn(true);

        $this->productImageMock->expects($this->any())
            ->method('getCountUsedProductImages')
            ->willReturn(1);
        $this->productImageMock->expects($this->any())
            ->method('getUsedProductImages')
            ->willReturnCallback(
                
                    function () {
                        $data = [[ 'filepath' => $this->testfilename ]];
                        foreach ($data as $e) {
                            yield $e;
                        }
                    }
                
            );

        $this->mediaDirectoryMock->expects($this->any())
            ->method('isFile')
            ->with($this->testfilepath)
            ->willReturn(true);

        $this->databaseMock->expects($this->once())
            ->method('saveFileToFilesystem')
            ->with($this->testfilepath);
        $this->databaseMock->expects($this->once())
            ->method('saveFile')
            ->with($this->testfilepath);

        $generator = $this->service->resizeFromThemes(['test-theme']);
        while ($generator->valid()) {
            $generator->next();
        }
    }

    public function testResizeFromImageNameMediaStorageDatabase()
    {
        $this->databaseMock->expects($this->any())
            ->method('checkDbUsage')
            ->willReturn(true);

        $this->mediaDirectoryMock->expects($this->any())
            ->method('isFile')
            ->with($this->testfilepath)
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(false),
                $this->returnValue(true)
            );

        $this->themeCollectionMock->expects($this->any())
            ->method('loadRegisteredThemes')
            ->willReturn(
                [ new DataObject(['id' => '0']) ]
            );
        $this->themeCustomizationConfigMock->expects($this->any())
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
}
