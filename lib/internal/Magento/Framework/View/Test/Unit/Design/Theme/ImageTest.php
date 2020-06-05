<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test theme image model
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Image\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Theme\Image;
use Magento\Framework\View\Design\Theme\Image\Uploader;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Image\Path;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends TestCase
{
    /**
     * @var Image
     */
    protected $_model;

    /**
     * @var Filesystem|MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \Magento\Framework\Image|MockObject
     */
    protected $_imageMock;

    /**
     * @var Uploader|MockObject
     */
    protected $_uploaderMock;

    /**
     * @var Theme|MockObject
     */
    protected $_themeMock;

    /**
     * @var Write|MockObject
     */
    protected $_mediaDirectoryMock;

    /**
     * @var Write|MockObject
     */
    protected $_rootDirectoryMock;

    /**
     * @var MockObject|Path
     */
    protected $imagePathMock;

    protected function setUp(): void
    {
        $this->_mediaDirectoryMock = $this->createPartialMock(
            Write::class,
            ['isExist', 'copyFile', 'getRelativePath', 'delete']
        );
        $this->_rootDirectoryMock = $this->createPartialMock(
            Write::class,
            ['isExist', 'copyFile', 'getRelativePath', 'delete']
        );
        $this->_filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->addMethods(['delete'])
            ->onlyMethods(['getDirectoryWrite'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_filesystemMock->expects($this->at(0))
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->_mediaDirectoryMock);
        $this->_filesystemMock->expects($this->at(1))
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->_rootDirectoryMock);
        $imageFactory = $this->createMock(Factory::class);
        $this->_imageMock = $this->createMock(\Magento\Framework\Image::class);
        $imageFactory->expects($this->any())->method('create')->willReturn($this->_imageMock);

        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->_themeMock = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPreviewImage'])
            ->getMock();
        $this->_uploaderMock = $this->createMock(Uploader::class);

        $this->imagePathMock = $this->_getImagePathMock();

        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(
            Image::class,
            [
                'filesystem' => $this->_filesystemMock,
                'imageFactory' => $imageFactory,
                'uploader' => $this->_uploaderMock,
                'themeImagePath' => $this->imagePathMock,
                'logger' => $logger,
                'theme' => $this->_themeMock
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->_model = null;
        $this->_filesystemMock = null;
        $this->_imageMock = null;
        $this->_uploaderMock = null;
        $this->_themeMock = null;
    }

    /**
     * @return MockObject|Path
     */
    protected function _getImagePathMock()
    {
        $imagePathMock = $this->createMock(Path::class);
        $testBaseUrl = 'http://localhost/media_path/';

        $imagePathMock->expects($this->any())->method('getPreviewImageDefaultUrl')
            ->willReturn($testBaseUrl . 'test_default_preview.png');

        $testBaseDir = '/media_path/';
        $imagePathMock->expects(
            $this->any()
        )->method(
            'getImagePreviewDirectory'
        )->willReturn(
            $testBaseDir . 'theme/preview'
        );
        $imagePathMock->expects(
            $this->any()
        )->method(
            'getTemporaryDirectory'
        )->willReturn(
            $testBaseDir . 'tmp'
        );
        return $imagePathMock;
    }

    /**
     * Sample theme data
     *
     * @return array
     */
    protected function _getThemeSampleData()
    {
        return [
            'theme_id' => 1,
            'theme_title' => 'Sample theme',
            'preview_image' => 'images/preview.png',
            'area' => Area::AREA_FRONTEND,
            'type' => ThemeInterface::TYPE_VIRTUAL
        ];
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::__construct
     */
    public function testConstructor()
    {
        $this->assertNotEmpty($this->_model);
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::createPreviewImage
     */
    public function testCreatePreviewImage()
    {
        $this->_themeMock->method('getPreviewImage')->willReturn(null);
        $this->_imageMock->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            '/media_path/theme/preview',
            $this->anything()
        );
        $this->_model->createPreviewImage('/some/path/to/image.png');
        $this->assertNotNull($this->_themeMock->getData('preview_image'));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::createPreviewImageCopy
     */
    public function testCreatePreviewImageCopy()
    {
        $previewImage = 'test_preview.png';
        $relativePath = '/media_path/theme/preview/test_preview.png';
        $this->_themeMock->method('getPreviewImage')->willReturn($previewImage);

        $this->_mediaDirectoryMock->expects($this->once())
            ->method('getRelativePath')
            ->willReturnArgument(0);

        $this->_rootDirectoryMock->expects($this->once())
            ->method('getRelativePath')
            ->with($previewImage)
            ->willReturn($relativePath);

        $this->_rootDirectoryMock->expects($this->once())
            ->method('copyFile')
            ->with($relativePath, $this->anything())
            ->willReturn(true);

        $themeImageMock = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPreviewImagePath'])
            ->getMock();
        $themeImageMock->expects($this->atLeastOnce())
            ->method('getPreviewImagePath')
            ->willReturn($previewImage);

        $themeMock = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->setMethods(['getThemeImage', 'getPreviewImage'])
            ->getMock();
        $themeMock->expects($this->atLeastOnce())
            ->method('getPreviewImage')
            ->willReturn($previewImage);
        $themeMock->expects($this->atLeastOnce())
            ->method('getThemeImage')
            ->willReturn($themeImageMock);

        $this->assertTrue($this->_model->createPreviewImageCopy($themeMock));
        $this->assertEquals($previewImage, $this->_themeMock->getData('preview_image'));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::removePreviewImage
     */
    public function testRemovePreviewImage()
    {
        $this->_themeMock->method('getPreviewImage')->willReturn('test.png');
        $this->_mediaDirectoryMock->expects($this->once())->method('delete');
        $this->_model->removePreviewImage();
        $this->assertNull($this->_themeMock->getData('preview_image'));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::removePreviewImage
     */
    public function testRemoveEmptyPreviewImage()
    {
        $this->_themeMock->method('getPreviewImage')->willReturn(null);
        $this->_filesystemMock->expects($this->never())->method('delete');
        $this->_model->removePreviewImage();
        $this->assertNull($this->_themeMock->getData('preview_image'));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::uploadPreviewImage
     */
    public function testUploadPreviewImage()
    {
        $scope = 'test_scope';
        $tmpFilePath = '/media_path/tmp/temporary.png';
        $this->_themeMock->method('getPreviewImage')->willReturn('test.png');
        $this->_uploaderMock->expects(
            $this->once()
        )->method(
            'uploadPreviewImage'
        )->with(
            $scope,
            '/media_path/tmp'
        )->willReturn(
            $tmpFilePath
        );

        $this->_mediaDirectoryMock->expects($this->at(0))->method('getRelativePath')->willReturnArgument(0);
        $this->_mediaDirectoryMock->expects($this->at(1))->method('delete')->with($this->stringContains('test.png'));

        $this->_mediaDirectoryMock->expects($this->at(2))->method('delete')->with($tmpFilePath);

        $this->_model->uploadPreviewImage($scope);
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::getPreviewImageUrl
     */
    public function testGetPreviewImageUrl()
    {
        $this->_themeMock->method('getPreviewImage')
            ->willReturn('...');
        $this->imagePathMock->expects($this->any())
            ->method('getPreviewImageUrl')
            ->with($this->_themeMock)
            ->willReturn('http://localhost/media_path/preview/image.png');

        $this->assertEquals('http://localhost/media_path/preview/image.png', $this->_model->getPreviewImageUrl());
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::getPreviewImageUrl
     */
    public function testGetDefaultPreviewImageUrl()
    {
        $this->_themeMock->method('getPreviewImage')->willReturn(null);
        $this->assertEquals(
            'http://localhost/media_path/test_default_preview.png',
            $this->_model->getPreviewImageUrl()
        );
    }
}
