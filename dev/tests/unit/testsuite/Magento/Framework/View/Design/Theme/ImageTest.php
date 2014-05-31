<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme image model
 */
namespace Magento\Framework\View\Design\Theme;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\Theme\Image
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    /**
     * @var \Magento\Framework\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imageMock;

    /**
     * @var \Magento\Framework\View\Design\Theme\Image\Uploader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_uploaderMock;

    /**
     * @var \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mediaDirectoryMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rootDirectoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Theme\Image\Path
     */
    protected $imagePathMock;

    protected function setUp()
    {
        $this->_mediaDirectoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array('isExist', 'copyFile', 'getRelativePath', 'delete'),
            array(),
            '',
            false,
            false
        );
        $this->_rootDirectoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array('isExist', 'copyFile', 'getRelativePath', 'delete'), array(), '', false, false
        );
        $this->_filesystemMock = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array('getDirectoryWrite', '__wakeup'),
            array(),
            '',
            false,
            false
        );
        $this->_filesystemMock->expects($this->at(0))
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem::MEDIA_DIR)
            ->will($this->returnValue($this->_mediaDirectoryMock));
        $this->_filesystemMock->expects($this->at(1))
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem::ROOT_DIR)
            ->will($this->returnValue($this->_rootDirectoryMock));
        $imageFactory = $this->getMock('Magento\Framework\Image\Factory', array(), array(), '', false, false);
        $this->_imageMock = $this->getMock('Magento\Framework\Image', array(), array(), '', false, false);
        $imageFactory->expects($this->any())->method('create')->will($this->returnValue($this->_imageMock));

        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false, false);
        $this->_themeMock = $this->getMock('Magento\Core\Model\Theme', array('__wakeup'), array(), '', false, false);
        $this->_uploaderMock = $this->getMock(
            'Magento\Framework\View\Design\Theme\Image\Uploader',
            array(),
            array(),
            '',
            false,
            false
        );

        $this->imagePathMock = $this->_getImagePathMock();

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\Framework\View\Design\Theme\Image', array(
            'filesystem' => $this->_filesystemMock,
            'imageFactory' => $imageFactory,
            'uploader' => $this->_uploaderMock,
            'themeImagePath' => $this->imagePathMock,
            'logger' => $logger,
            'theme' => $this->_themeMock
        ));
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_filesystemMock = null;
        $this->_imageMock = null;
        $this->_uploaderMock = null;
        $this->_themeMock = null;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Core\Model\Theme\Image\Path
     */
    protected function _getImagePathMock()
    {
        $imagePathMock = $this->getMock('Magento\Core\Model\Theme\Image\Path', array(), array(), '', false);
        $testBaseUrl = 'http://localhost/media_path/';

        $imagePathMock->expects($this->any())->method('getPreviewImageDefaultUrl')
            ->will($this->returnValue($testBaseUrl . 'test_default_preview.png'));

        $testBaseDir = '/media_path/';
        $imagePathMock->expects(
            $this->any()
        )->method(
            'getImagePreviewDirectory'
        )->will(
            $this->returnValue($testBaseDir . 'theme/preview')
        );
        $imagePathMock->expects(
            $this->any()
        )->method(
            'getTemporaryDirectory'
        )->will(
            $this->returnValue($testBaseDir . 'tmp')
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
        return array(
            'theme_id' => 1,
            'theme_title' => 'Sample theme',
            'preview_image' => 'images/preview.png',
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
        );
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
            ->will($this->returnArgument(0));

        $this->_rootDirectoryMock->expects($this->once())
            ->method('getRelativePath')
            ->with($previewImage)
            ->will($this->returnValue($relativePath));

        $this->_rootDirectoryMock->expects($this->once())
            ->method('copyFile')
            ->with($relativePath, $this->anything())
            ->will($this->returnValue(true));

        $themeImageMock = $this->getMockBuilder('Magento\Framework\View\Design\Theme\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getPreviewImagePath'])
            ->getMock();
        $themeImageMock->expects($this->atLeastOnce())
            ->method('getPreviewImagePath')
            ->will($this->returnValue($previewImage));

        $themeMock = $this->getMockBuilder('Magento\Core\Model\Theme')
            ->disableOriginalConstructor()
            ->setMethods(['getThemeImage', 'getPreviewImage', '__wakeup'])
            ->getMock();
        $themeMock->expects($this->atLeastOnce())
            ->method('getPreviewImage')
            ->will($this->returnValue($previewImage));
        $themeMock->expects($this->atLeastOnce())
            ->method('getThemeImage')
            ->will($this->returnValue($themeImageMock));

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
        )->will(
            $this->returnValue($tmpFilePath)
        );

        $this->_mediaDirectoryMock->expects($this->at(0))->method('getRelativePath')->will($this->returnArgument(0));
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
            ->will($this->returnValue('http://localhost/media_path/preview/image.png'));

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
