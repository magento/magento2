<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test of image path model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Image;

use \Magento\Theme\Model\Theme\Image\Path;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Design\Theme\Image\PathInterface;

class PathTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\Theme\Image\Path|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    protected function setUp()
    {
        $this->filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->mediaDirectory = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->_assetRepo = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $this->_storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);

        $this->mediaDirectory->expects($this->any())
            ->method('getRelativePath')
            ->with('/theme/origin')
            ->will($this->returnValue('/theme/origin'));

        $this->filesystem->expects($this->any())->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue($this->mediaDirectory));

        $this->model = new Path(
            $this->filesystem,
            $this->_assetRepo,
            $this->_storeManager
        );

        $this->_model = new Path($this->filesystem, $this->_assetRepo, $this->_storeManager);
    }

    public function testGetPreviewImageUrl()
    {
        /** @var $theme \Magento\Theme\Model\Theme|\PHPUnit_Framework_MockObject_MockObject */
        $theme = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['getPreviewImage', 'isPhysical', '__wakeup']);
        $theme->expects($this->any())
            ->method('getPreviewImage')
            ->will($this->returnValue('image.png'));

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://localhost/'));
        $this->_storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->assertEquals('http://localhost/theme/preview/image.png', $this->model->getPreviewImageUrl($theme));
    }

    public function testGetPreviewImagePath()
    {
        $previewImage = 'preview.jpg';
        $expectedPath = 'theme/preview/preview.jpg';

        /** @var $theme \Magento\Theme\Model\Theme|\PHPUnit_Framework_MockObject_MockObject */
        $theme = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['getPreviewImage', 'isPhysical', '__wakeup']);

        $this->mediaDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with(PathInterface::PREVIEW_DIRECTORY_PATH . '/' . $previewImage)
            ->willReturn($expectedPath);

        $theme->expects($this->once())
            ->method('getPreviewImage')
            ->will($this->returnValue($previewImage));

        $result = $this->model->getPreviewImagePath($theme);

        $this->assertEquals($expectedPath, $result);
    }

    /**
     * @covers Magento\Theme\Model\Theme\Image\Path::getPreviewImageDefaultUrl
     */
    public function testDefaultPreviewImageUrlGetter()
    {
        $this->_assetRepo->expects($this->once())->method('getUrl')
            ->with(\Magento\Theme\Model\Theme\Image\Path::DEFAULT_PREVIEW_IMAGE);
        $this->model->getPreviewImageDefaultUrl();
    }

    /**
     * @covers \Magento\Theme\Model\Theme\Image\Path::getImagePreviewDirectory
     */
    public function testImagePreviewDirectoryGetter()
    {
        $this->mediaDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(\Magento\Framework\View\Design\Theme\Image\PathInterface::PREVIEW_DIRECTORY_PATH)
            ->will($this->returnValue('/theme/preview'));
        $this->assertEquals(
            '/theme/preview',
            $this->model->getImagePreviewDirectory()
        );
    }

    /**
     * @covers \Magento\Theme\Model\Theme\Image\Path::getTemporaryDirectory
     */
    public function testTemporaryDirectoryGetter()
    {
        $this->assertEquals(
            '/theme/origin',
            $this->model->getTemporaryDirectory()
        );
    }
}
