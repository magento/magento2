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
        $this->_urlBuilder = $this->getMock('Magento\Backend\Model\Url', array(), array(), '', false);
        $this->_themeContext = $this->getMock('Magento\DesignEditor\Model\Theme\Context', array(), array(), '', false);
        $this->_theme = $this->getMock(
            'Magento\Core\Model\Theme',
            array('getId', 'getCustomization', '__wakeup'),
            array(),
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

        $this->_helperMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_model = $objectManagerHelper->getObject(
            'Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code\Js',
            array(
                'urlBuilder' => $this->_urlBuilder,
                'themeContext' => $this->_themeContext,
                'formFactory' => $this->getMock('Magento\Framework\Data\FormFactory', array(), array(), '', false),
                'coreHelper' => $this->_helperMock
            )
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
            array('theme_id' => self::TEST_THEME_ID)
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
            array('theme_id' => self::TEST_THEME_ID)
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
            array(),
            array(),
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
            $this->returnValue(array())
        );

        $customization->expects(
            $this->once()
        )->method(
            'generateFileInfo'
        )->with(
            array()
        )->will(
            $this->returnValue(array('js' => 'files'))
        );

        $this->_helperMock->expects(
            $this->once()
        )->method(
            'jsonEncode'
        )->with(
            array('js' => 'files')
        )->will(
            $this->returnValue('someData')
        );

        $this->assertEquals('someData', $this->_model->getFiles());
    }
}
