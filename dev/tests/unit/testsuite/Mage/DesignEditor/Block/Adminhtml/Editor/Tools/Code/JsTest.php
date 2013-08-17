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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_JsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Theme id of virtual theme
     */
    const TEST_THEME_ID = 15;

    /**
     * @var Mage_Backend_Model_Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var Mage_DesignEditor_Model_Theme_Context|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeContext;

    /**
     * @var Mage_Core_Model_Theme|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_theme;

    /**
     * @var Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    public function setUp()
    {
        $this->_urlBuilder = $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false);
        $this->_themeContext = $this->getMock('Mage_DesignEditor_Model_Theme_Context', array(), array(), '', false);
        $this->_theme = $this->getMock('Mage_Core_Model_Theme', array('getId', 'getCustomization'), array(), '', false);
        $this->_theme->expects($this->any())->method('getId')->will($this->returnValue(self::TEST_THEME_ID));
        $this->_themeContext->expects($this->any())->method('getEditableTheme')
            ->will($this->returnValue($this->_theme));
        $this->_themeContext->expects($this->any())->method('getStagingTheme')
            ->will($this->returnValue($this->_theme));

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js',
            array(
                'urlBuilder' => $this->_urlBuilder,
                'themeContext' => $this->_themeContext
        ));
        $this->_model = $this->getMock(
            'Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js',
            array('__', 'helper'),
            $constructArguments
        );
    }

    public function tearDown()
    {
        $this->_model = null;
        $this->_urlBuilder = null;
        $this->_themeContext = null;
        $this->_theme = null;
    }

    /**
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js::getJsUploadUrl
     */
    public function testGetDownloadCustomCssUrl()
    {
        $expectedUrl = 'some_url';
        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/system_design_editor_tools/uploadjs', array('theme_id' => self::TEST_THEME_ID))
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_model->getJsUploadUrl());
    }

    /**
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js::getJsReorderUrl
     */
    public function testGetJsReorderUrl()
    {
        $expectedUrl = 'some_url';
        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/system_design_editor_tools/reorderjs', array('theme_id' => self::TEST_THEME_ID))
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_model->getJsReorderUrl());
    }

    /**
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js::getTitle
     */
    public function testGetTitle()
    {
        $this->_model->expects($this->atLeastOnce())
            ->method('__')
            ->will($this->returnArgument(0));
        $this->assertEquals('Custom javascript files', $this->_model->getTitle());
    }

    /**
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js::getFiles
     */
    public function testGetJsFiles()
    {
        $customization = $this->getMock('Mage_Core_Model_Theme_Customization', array(), array(), '', false);
        $this->_theme->expects($this->any())->method('getCustomization')->will($this->returnValue($customization));

        $customization->expects($this->once())
            ->method('getFilesByType')
            ->with(Mage_Core_Model_Theme_Customization_File_Js::TYPE)
            ->will($this->returnValue(array()));
        $helperMock = $this->getMock('Mage_Core_Helper_Data', array(), array(), '', false);
        $this->_model->expects($this->once())->method('helper')->with('Mage_Core_Helper_Data')
            ->will($this->returnValue($helperMock));

        $this->_model->getFiles();
    }
}
