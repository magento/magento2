<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Storage helper test
 */
namespace Magento\Theme\Helper;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Theme\Helper\Storage
     */
    protected $helper;

    /**
     * @var string
     */
    protected $customizationPath;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryWrite;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextHelper;

    /**
     * @var \Magento\Core\Model\Theme|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $theme;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customization;

    protected $requestParams;

    protected function setUp()
    {
        $this->customizationPath = '/' . implode('/', ['var', 'theme']);

        $this->request = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->session = $this->getMock('Magento\Backend\Model\Session', [], [], '', false);
        $this->contextHelper = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->directoryWrite = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            [],
            [],
            '',
            false
        );
        $this->themeFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\FlyweightFactory',
            [],
            [],
            '',
            false
        );
        $this->theme = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);
        $this->customization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            [],
            [],
            '',
            false
        );

        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->directoryWrite));

        $this->directoryWrite->expects($this->any())->method('create')->will($this->returnValue(true));
        $this->contextHelper->expects($this->once())->method('getRequest')->will($this->returnValue($this->request));
        $this->themeFactory->expects($this->any())->method('create')->will($this->returnValue($this->theme));

        $this->theme->expects($this->any())
            ->method('getCustomization')
            ->will($this->returnValue($this->customization));

        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with(\Magento\Theme\Helper\Storage::PARAM_THEME_ID)
            ->will($this->returnValue(6));
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with(\Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE)
            ->will($this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE));

        $this->helper = new \Magento\Theme\Helper\Storage(
            $this->contextHelper,
            $this->filesystem,
            $this->session,
            $this->themeFactory
        );
    }

    protected function tearDown()
    {
        $this->request = null;
        $this->filesystem = null;
        $this->session = null;
        $this->contextHelper = null;
        $this->directoryWrite = null;
        $this->themeFactory = null;
        $this->theme = null;
        $this->customization = null;
    }

    /**
     * @covers \Magento\Theme\Helper\Storage::getShortFilename
     */
    public function testGetShortFilename()
    {
        $longFileName = 'veryLongFileNameMoreThanTwenty';
        $expectedFileName = 'veryLongFileNameMore...';
        $this->assertEquals($expectedFileName, $this->helper->getShortFilename($longFileName, 20));
    }

    public function testGetStorageRoot()
    {
        $expectedStorageRoot = '/' . \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE;
        $this->assertEquals($expectedStorageRoot, $this->helper->getStorageRoot());
    }

    public function testGetThumbnailDirectory()
    {
        $imagePath = implode('/', ['root', 'image', 'image_name.jpg']);
        $thumbnailDir = implode(
            '/',
            ['root', 'image', \Magento\Theme\Model\Wysiwyg\Storage::THUMBNAIL_DIRECTORY]
        );

        $this->assertEquals($thumbnailDir, $this->helper->getThumbnailDirectory($imagePath));
    }

    public function testGetThumbnailPath()
    {
        $image = 'image_name.jpg';
        $thumbnailPath = '/' . implode(
            '/',
            [
                \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
                \Magento\Theme\Model\Wysiwyg\Storage::THUMBNAIL_DIRECTORY,
                $image
            ]
        );

        $this->customization->expects(
            $this->any()
        )->method(
            'getCustomizationPath'
        )->will(
            $this->returnValue($this->customizationPath)
        );

        $this->directoryWrite->expects($this->any())->method('isExist')->will($this->returnValue(true));

        $this->assertEquals($thumbnailPath, $this->helper->getThumbnailPath($image));
    }

    public function testGetRequestParams()
    {
        $this->request->expects(
            $this->at(0)
        )->method(
            'getParam'
        )->with(
            \Magento\Theme\Helper\Storage::PARAM_THEME_ID
        )->will(
            $this->returnValue(6)
        );
        $this->request->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE
        )->will(
            $this->returnValue('image')
        );
        $this->request->expects(
            $this->at(2)
        )->method(
            'getParam'
        )->with(
            \Magento\Theme\Helper\Storage::PARAM_NODE
        )->will(
            $this->returnValue('node')
        );

        $expectedResult = [
            \Magento\Theme\Helper\Storage::PARAM_THEME_ID => 6,
            \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE => \Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE,
            \Magento\Theme\Helper\Storage::PARAM_NODE => 'node',
        ];
        $this->assertEquals($expectedResult, $this->helper->getRequestParams());
    }

    public function testGetAllowedExtensionsByType()
    {
        $this->request->expects(
            $this->at(0)
        )->method(
            'getParam'
        )->with(
            \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE
        )->will(
            $this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_FONT)
        );

        $this->request->expects(
            $this->at(1)
        )->method(
            'getParam'
        )->with(
            \Magento\Theme\Helper\Storage::PARAM_CONTENT_TYPE
        )->will(
            $this->returnValue(\Magento\Theme\Model\Wysiwyg\Storage::TYPE_IMAGE)
        );

        $fontTypes = $this->helper->getAllowedExtensionsByType();
        $this->assertEquals(['ttf', 'otf', 'eot', 'svg', 'woff'], $fontTypes);

        $imagesTypes = $this->helper->getAllowedExtensionsByType();
        $this->assertEquals(['jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'], $imagesTypes);
    }
}
