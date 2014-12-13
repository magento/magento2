<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Test of image path model
 */
namespace Magento\Core\Model\Theme\Image;

use Magento\Framework\App\Filesystem\DirectoryList;

class PathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Theme\Image\Path|\PHPUnit_Framework_MockObject_MockObject
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
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->mediaDirectory = $this->getMock(
            'Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false
        );
        $this->_assetRepo = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);

        $this->mediaDirectory->expects($this->any())
            ->method('getAbsolutePath')
            ->with(\Magento\Framework\View\Design\Theme\Image\PathInterface::PREVIEW_DIRECTORY_PATH)
            ->will($this->returnValue('/theme/preview'));

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

    public function testGetPreviewImageUrlPhysicalTheme()
    {
        $theme = $this->getGetTheme(true);

        $this->_assetRepo->expects($this->any())
            ->method('getUrlWithParams')
            ->with($theme->getPreviewImage(), ['area' => $theme->getData('area'), 'themeModel' => $theme])
            ->will($this->returnValue('http://localhost/theme/preview/image.png'));

        $this->assertEquals('http://localhost/theme/preview/image.png', $this->model->getPreviewImageUrl($theme));
    }

    public function testGetPreviewImageUrlVirtualTheme()
    {
        $theme = $this->getGetTheme(false);

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://localhost/'));
        $this->_storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->assertEquals('http://localhost/theme/preview/image.png', $this->model->getPreviewImageUrl($theme));
    }

    /**
     * @param bool $isPhysical
     * @return \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGetTheme($isPhysical)
    {
        /** @var $theme \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject */
        $theme = $this->getMock(
            'Magento\Core\Model\Theme',
            ['getPreviewImage', 'isPhysical', '__wakeup'],
            [],
            '',
            false
        );

        $theme->setData('area', 'frontend');

        $theme->expects($this->any())
            ->method('isPhysical')
            ->will($this->returnValue($isPhysical));

        $theme->expects($this->any())
            ->method('getPreviewImage')
            ->will($this->returnValue('image.png'));

        return $theme;
    }

    /**
     * @covers Magento\Core\Model\Theme\Image\Path::getPreviewImageDefaultUrl
     */
    public function testDefaultPreviewImageUrlGetter()
    {
        $this->_assetRepo->expects($this->once())->method('getUrl')
            ->with(\Magento\Core\Model\Theme\Image\Path::DEFAULT_PREVIEW_IMAGE);
        $this->model->getPreviewImageDefaultUrl();
    }

    /**
     * @covers \Magento\Core\Model\Theme\Image\Path::getImagePreviewDirectory
     */
    public function testImagePreviewDirectoryGetter()
    {
        $this->assertEquals(
            '/theme/preview',
            $this->model->getImagePreviewDirectory()
        );
    }

    /**
     * @covers \Magento\Core\Model\Theme\Image\Path::getTemporaryDirectory
     */
    public function testTemporaryDirectoryGetter()
    {
        $this->assertEquals(
            '/theme/origin',
            $this->model->getTemporaryDirectory()
        );
    }
}
