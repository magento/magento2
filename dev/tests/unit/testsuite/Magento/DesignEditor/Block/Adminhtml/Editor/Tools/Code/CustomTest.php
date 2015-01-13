<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code;

class CustomTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Custom|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

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

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Custom',
            [
                'config' => $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface'),
                'formFactory' => $this->getMock('Magento\Framework\Data\FormFactory', [], [], '', false),
                'urlBuilder' => $this->_urlBuilder,
                'themeContext' => $this->_themeContext
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
     * @covers \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Custom::getDownloadCustomCssUrl
     */
    public function testGetDownloadCustomCssUrl()
    {
        $expectedUrl = 'some_url';

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_design_theme/downloadCustomCss',
            ['theme_id' => self::TEST_THEME_ID]
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->assertEquals($expectedUrl, $this->_model->getDownloadCustomCssUrl());
    }

    public function testGetSaveCustomCssUrl()
    {
        $expectedUrl = 'some_url';

        $this->_urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_design_editor_tools/saveCssContent',
            ['theme_id' => self::TEST_THEME_ID]
        )->will(
            $this->returnValue($expectedUrl)
        );

        $this->assertEquals($expectedUrl, $this->_model->getSaveCustomCssUrl());
    }

    public function testGetCustomCssContent()
    {
        $expectedContent = 'New file content';

        $customization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            [],
            [],
            '',
            false
        );
        $this->_theme->expects($this->any())->method('getCustomization')->will($this->returnValue($customization));

        /** @var $cssFile \Magento\Framework\View\Design\Theme\Customization\File\Css */
        $cssFile = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization\File\Css',
            ['getContent'],
            [],
            '',
            false
        );

        $customization->expects(
            $this->once()
        )->method(
            'getFilesByType'
        )->with(
            \Magento\Theme\Model\Theme\Customization\File\CustomCss::TYPE
        )->will(
            $this->returnValue([$cssFile])
        );

        $cssFile->expects($this->once())->method('getContent')->will($this->returnValue('New file content'));

        $this->assertEquals($expectedContent, $this->_model->getCustomCssContent());
    }
}
