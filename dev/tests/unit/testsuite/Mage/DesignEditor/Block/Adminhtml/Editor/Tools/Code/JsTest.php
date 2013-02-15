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
     * @var Mage_Backend_Model_Url|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    public function setUp()
    {
        $this->_urlBuilder = $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false);

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            Magento_Test_Helper_ObjectManager::BLOCK_ENTITY,
            'Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js',
            array(
                 'config'     => $this->getMock('Mage_Core_Model_Config', array(), array(), '', false),
                 'service'    => $this->getMock('Mage_Core_Model_Theme_Service', array(), array(), '', false),
                 'urlBuilder' => $this->_urlBuilder
        ));
        $this->_model = $this->getMock(
            'Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js',
            array('__'),
            $constructArguments
        );
    }

    public function tearDown()
    {
        $this->_model = null;
        $this->_urlBuilder = null;
    }

    /**
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js::getJsUploadUrl
     */
    public function testGetDownloadCustomCssUrl()
    {
        $themeId = 15;
        $theme = $this->getMockBuilder('Mage_Core_Model_Theme')->disableOriginalConstructor()->getMock();
        $theme->expects($this->once())->method('getId')->will($this->returnValue($themeId));

        $this->_model->setTheme($theme);
        $expectedUrl = 'some_url';

        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/system_design_editor_tools/uploadjs', array('id' => $themeId))
            ->will($this->returnValue($expectedUrl));

        $this->assertEquals($expectedUrl, $this->_model->getJsUploadUrl());
    }

    /**
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js::getJsReorderUrl
     */
    public function testGetJsReorderUrl()
    {
        $themeId = 8;
        $theme = $this->getMockBuilder('Mage_Core_Model_Theme')->disableOriginalConstructor()->getMock();
        $theme->expects($this->once())->method('getId')->will($this->returnValue($themeId));
        $this->_model->setTheme($theme);

        $expectedUrl = 'some_url';
        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/system_design_editor_tools/reorderjs', array('id' => $themeId))
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
     * @covers Mage_DesignEditor_Block_Adminhtml_Editor_Tools_Code_Js::getJsFiles
     */
    public function testGetJsFiles()
    {
        $filesCollection = $this->getMockBuilder('Mage_Core_Model_Resource_Theme_Files_Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $theme = $this->getMockBuilder('Mage_Core_Model_Theme')
            ->disableOriginalConstructor()
            ->setMethods(array('getCustomizationData'))
            ->getMock();

        $theme->expects($this->once())
            ->method('getCustomizationData')
            ->with(Mage_Core_Model_Theme_Customization_Files_Js::TYPE)
            ->will($this->returnValue($filesCollection));

        $this->_model->setTheme($theme);
        $this->assertEquals($filesCollection, $this->_model->getJsFiles());
    }
}
