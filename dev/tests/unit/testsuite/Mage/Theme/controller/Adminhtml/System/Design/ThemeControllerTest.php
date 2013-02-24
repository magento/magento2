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
 * @category    Magento
 * @package     Mage_Theme
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require 'Mage/Theme/controllers/Adminhtml/System/Design/ThemeController.php';
/**
 * Test backend controller for the theme
 */
class Mage_Theme_Controller_Adminhtml_System_Design_ThemeControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Theme_Adminhtml_System_Design_ThemeController
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager_Zend',
            array('create', 'get'), array(), '', false);

        $this->_request = $this->getMock(
            'Mage_Core_Controller_Request_Http', array('getParam', 'getPost'), array(), '', false
        );

        $this->_model = $this->getMock('Mage_Theme_Adminhtml_System_Design_ThemeController',
            array('_forward', '_title', '__', 'loadLayout', 'renderLayout', '_redirect', '_getSession'),
            array(
                $this->_request,
                $this->getMock('Mage_Core_Controller_Response_Http', array(), array(), '', false),
                $this->_objectManagerMock,
                $this->getMock('Mage_Core_Controller_Varien_Front', array(), array(), '', false),
                $this->getMock('Mage_Core_Model_Layout_Factory', array(), array(), '', false),
                null,
                array(
                    'translator' => 'translator',
                    'helper'     => 'helper',
                    'session'    => 'session'
                ),
            ));
        $this->_model->expects($this->any())->method('_title')->will($this->returnValue($this->_model));
        $this->_model->expects($this->any())->method('loadLayout');
        $this->_model->expects($this->any())->method('renderLayout');
        $this->_model->expects($this->any())->method('__');

        $sessionMock = $this->getMock('Mage_Backend_Model_Session', array('addSuccess'), array(), '', false);
        $this->_model->expects($this->any())->method('_getSession')->will($this->returnValue($sessionMock));
    }

    /**
     * @covers Mage_Theme_Adminhtml_System_Design_ThemeController::saveAction
     */
    public function testSaveAction()
    {
        $themeData = 'theme data';
        $customCssContent = 'custom css content';
        $jsUploadedFiles = array(1, 2);
        $jsRemovedFiles = array(3, 4);
        $jsOrder = array(1 => '1', 2 => 'test');

        $this->_request->expects($this->at(0))->method('getParam')->with('back', false)
            ->will($this->returnValue(true));

        $this->_request->expects($this->at(1))->method('getParam')->with('theme')
            ->will($this->returnValue($themeData));
        $this->_request->expects($this->at(2))->method('getParam')->with('custom_css_content')
            ->will($this->returnValue($customCssContent));
        $this->_request->expects($this->at(3))->method('getParam')->with('js_uploaded_files')
            ->will($this->returnValue($jsUploadedFiles));
        $this->_request->expects($this->at(4))->method('getParam')->with('js_removed_files')
            ->will($this->returnValue($jsRemovedFiles));
        $this->_request->expects($this->at(5))->method('getParam')->with('js_order')
            ->will($this->returnValue($jsOrder));
        $this->_request->expects($this->once(6))->method('getPost')->will($this->returnValue(true));

        $filesCssMock = $this->getMock(
            'Mage_Core_Model_Theme_Customization_Files_Css', array('setDataForSave'), array(), '', false
        );
        $filesCssMock->expects($this->at(0))->method('setDataForSave')->with($customCssContent);

        $filesJsMock = $this->getMock(
            'Mage_Core_Model_Theme_Customization_Files_Js',
            array('setDataForSave', 'setDataForDelete', 'setJsOrderData'),
            array(),
            '',
            false
        );
        $filesJsMock->expects($this->at(0))->method('setDataForSave')->with($jsUploadedFiles);
        $filesJsMock->expects($this->at(1))->method('setDataForDelete')->with($jsRemovedFiles);
        $filesJsMock->expects($this->at(2))->method('setJsOrderData')->with(array_keys($jsOrder));

        $themeMock = $this->getMock(
            'Mage_Core_Model_Theme', array('setCustomization', 'saveFormData'), array(), '', false
        );
        $themeMock->expects($this->at(0))->method('setCustomization')->with($filesCssMock);
        $themeMock->expects($this->at(1))->method('setCustomization')->with($filesJsMock);
        $themeMock->expects($this->at(2))->method('saveFormData')->with($themeData);

        $this->_objectManagerMock
            ->expects($this->at(0))
            ->method('create')
            ->with('Mage_Core_Model_Theme')
            ->will($this->returnValue($themeMock));

        $this->_objectManagerMock
            ->expects($this->at(1))
            ->method('create')
            ->with('Mage_Core_Model_Theme_Customization_Files_Css')
            ->will($this->returnValue($filesCssMock));

        $this->_objectManagerMock
            ->expects($this->at(2))
            ->method('create')
            ->with('Mage_Core_Model_Theme_Customization_Files_Js')
            ->will($this->returnValue($filesJsMock));

        $this->_model->saveAction();
    }

}
