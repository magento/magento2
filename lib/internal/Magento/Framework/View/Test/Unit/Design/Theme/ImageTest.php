<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme image model
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\Image
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \Magento\Framework\Image|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_imageMock;

    /**
     * @var \Magento\Framework\View\Design\Theme\Image\Uploader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_uploaderMock;

    /**
     * @var \Magento\Theme\Model\Theme|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_themeMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_mediaDirectoryMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_rootDirectoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Theme\Model\Theme\Image\Path
     */
    protected $imagePathMock;

    protected function setUp(): void
    {
        $this->_mediaDirectoryMock = $this->createPartialMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['isExist', 'copyFile', 'getRelativePath', 'delete']
        );
        $this->_rootDirectoryMock = $this->createPartialMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
            ['isExist', 'copyFile', 'getRelativePath', 'delete']
        );
        $this->_filesystemMock = $this->createPartialMock(
            \Magento\Framework\Filesystem::class,
            ['getDirectoryWrite', '__wakeup', 'delete']
        );
        $this->_filesystemMock->expects($this->at(0))
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->_mediaDirectoryMock);
        $this->_filesystemMock->expects($this->at(1))
            ->method('getDirectoryWrite')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->_rootDirectoryMock);
        $imageFactory = $this->createMock(\Magento\Framework\Image\Factory::class);
        $this->_imageMock = $this->createMock(\Magento\Framework\Image::class);
        $imageFactory->expects($this->any())->method('create')->willReturn($this->_imageMock);

        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->_themeMock = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup']);
        $this->_uploaderMock = $this->createMock(\Magento\Framework\View\Design\Theme\Image\Uploader::class);

        $this->imagePathMock = $this->_getImagePathMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject(
            \Magento\Framework\View\Design\Theme\Image::class,
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Theme\Model\Theme\Image\Path
     */
    protected function _getImagePathMock()
    {
        $imagePathMock = $this->createMock(\Magento\Theme\Model\Theme\Image\Path::class);
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
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
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
        $this->_themeMock->setData($this->_getThemeSampleData());
        $this->_themeMock->setData('preview_image', null);
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
        $this->_themeMock->setData($this->_getThemeSampleData());
        $this->_themeMock->setData('preview_image', $previewImage);

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

        $themeImageMock = $this->getMockBuilder(\Magento\Framework\View\Design\Theme\Image::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPreviewImagePath'])
            ->getMock();
        $themeImageMock->expects($this->atLeastOnce())
            ->method('getPreviewImagePath')
            ->willReturn($previewImage);

        $themeMock = $this->getMockBuilder(\Magento\Theme\Model\Theme::class)
            ->disableOriginalConstructor()
            ->setMethods(['getThemeImage', 'getPreviewImage', '__wakeup'])
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
        $this->_themeMock->setData($this->_getThemeSampleData());
        $this->_themeMock->setData('preview_image', 'test.png');
        $this->_mediaDirectoryMock->expects($this->once())->method('delete');
        $this->_model->removePreviewImage();
        $this->assertNull($this->_themeMock->getData('preview_image'));
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\Image::removePreviewImage
     */
    public function testRemoveEmptyPreviewImage()
    {
        $this->_themeMock->setData($this->_getThemeSampleData());
        $this->_themeMock->setData('preview_image', null);
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
        $this->_themeMock->setData($this->_getThemeSampleData());
        $this->_themeMock->setData('preview_image', 'test.png');
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
        $this->_themeMock->setData($this->_getThemeSampleData());

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
        $this->_themeMock->setData($this->_getThemeSampleData());
        $this->_themeMock->setData('preview_image', null);
        $this->assertEquals(
            'http://localhost/media_path/test_default_preview.png',
            $this->_model->getPreviewImageUrl()
        );
    }
}
