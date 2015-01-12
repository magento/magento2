<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code;

class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Theme id of virtual theme
     */
    const TEST_THEME_ID = 15;

    /**
     * @var \Magento\Backend\Model\Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\DesignEditor\Model\Theme\Context|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeContext;

    /**
     * @var \Magento\Core\Model\Theme|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_theme;

    /**
     * @var \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Js|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    protected function setUp()
    {
        $this->_urlBuilder = $this->getMock('Magento\Backend\Model\Url', [], [], '', false);
        $this->_themeContext = $this->getMock('Magento\DesignEditor\Model\Theme\Context', [], [], '', false);
        $this->_theme = $this->getMock(
            'Magento\Core\Model\Theme',
            ['getId', 'getCustomization', '__wakeup'],
            [],
            '',
            false
        );
        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(self::TEST_THEME_ID));
        $this->_themeContext->expects(
            $this->any()
        )->method(
            'getEditableTheme'
        )->will(
            $this->returnValue($this->_theme)
        );
        $this->_themeContext->expects(
            $this->any()
        )->method(
            'getStagingTheme'
        )->will(
            $this->returnValue($this->_theme)
        );

        $this->_helperMock = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_model = $objectManagerHelper->getObject(
            'Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Js',
            [
                'urlBuilder' => $this->_urlBuilder,
                'themeContext' => $this->_themeContext,
                'formFactory' => $this->getMock('Magento\Framework\Data\FormFactory', [], [], '', false),
                'coreHelper' => $this->_helperMock
            ]
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_urlBuilder = null;
        $this->_themeContext = null;
        $this->_theme = null;
    }

    /**
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Js::getJsUploadUrl
     */
    public function testGetDownloadCustomCssUrl()
    {
        $expectedUrl = 'some_url';
        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_design_editor_tools/uploadjs',
            ['theme_id' => self::TEST_THEME_ID]
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->assertEquals($expectedUrl, $this->_model->getJsUploadUrl());
    }

    /**
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Js::getJsReorderUrl
     */
    public function testGetJsReorderUrl()
    {
        $expectedUrl = 'some_url';
        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_design_editor_tools/reorderjs',
            ['theme_id' => self::TEST_THEME_ID]
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->assertEquals($expectedUrl, $this->_model->getJsReorderUrl());
    }

    /**
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Js::getTitle
     */
    public function testGetTitle()
    {
        $this->assertEquals('Custom javascript files', $this->_model->getTitle());
    }

    /**
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Js::getFiles
     */
    public function testGetJsFiles()
    {
        $customization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            [],
            [],
            '',
            false
        );
        $this->_theme->expects($this->any())->method('getCustomization')->will($this->returnValue($customization));

        $customization->expects(
            $this->once()
        )->method(
            'getFilesByType'
        )->with(
            \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE
        )->will(
            $this->returnValue([])
        );

        $customization->expects(
            $this->once()
        )->method(
            'generateFileInfo'
        )->with(
            []
        )->will(
            $this->returnValue(['js' => 'files'])
        );

        $this->_helperMock->expects(
            $this->once()
        )->method(
            'jsonEncode'
        )->with(
            ['js' => 'files']
        )->will(
            $this->returnValue('someData')
        );

        $this->assertEquals('someData', $this->_model->getFiles());
    }
}
