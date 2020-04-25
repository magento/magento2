<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test of image path model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\Theme\Image\PathInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Image\Path;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    /**
     * @var Path|MockObject
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $filesystem;

    /**
     * @var MockObject|Repository
     */
    protected $_assetRepo;

    /**
     * @var MockObject|StoreManager
     */
    protected $_storeManager;

    /**
     * @var MockObject|ReadInterface
     */
    protected $mediaDirectory;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->mediaDirectory = $this->createMock(ReadInterface::class);
        $this->_assetRepo = $this->createMock(Repository::class);
        $this->_storeManager = $this->createMock(StoreManager::class);

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
        /** @var Theme|\PHPUnit\Framework\MockObject\MockObject $theme */
        $theme = $this->createPartialMock(
            Theme::class,
            ['getPreviewImage', 'isPhysical', '__wakeup']
        );
        $theme->expects($this->any())
            ->method('getPreviewImage')
            ->will($this->returnValue('image.png'));

        $store = $this->createMock(Store::class);
        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://localhost/'));
        $this->_storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->assertEquals('http://localhost/theme/preview/image.png', $this->model->getPreviewImageUrl($theme));
    }

    public function testGetPreviewImagePath()
    {
        $previewImage = 'preview.jpg';
        $expectedPath = 'theme/preview/preview.jpg';

        /** @var Theme|\PHPUnit\Framework\MockObject\MockObject $theme */
        $theme = $this->createPartialMock(
            Theme::class,
            ['getPreviewImage', 'isPhysical', '__wakeup']
        );

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
            ->with(Path::DEFAULT_PREVIEW_IMAGE);
        $this->model->getPreviewImageDefaultUrl();
    }

    /**
     * @covers \Magento\Theme\Model\Theme\Image\Path::getImagePreviewDirectory
     */
    public function testImagePreviewDirectoryGetter()
    {
        $this->mediaDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(PathInterface::PREVIEW_DIRECTORY_PATH)
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
