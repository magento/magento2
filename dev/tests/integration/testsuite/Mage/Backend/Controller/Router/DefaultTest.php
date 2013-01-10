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
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Controller_Router_DefaultTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Controller_Router_Default
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontMock;

    protected function setUp()
    {
        $options = array(
            'areaCode'        => Mage::helper('Mage_Backend_Helper_Data')->getAreaCode(),
            'baseController'  => 'Mage_Backend_Controller_ActionAbstract',
        );
        $this->_frontMock = $this->getMock('Mage_Core_Controller_Varien_Front', array(), array(), '', false);
        $this->_model = Mage::getModel('Mage_Backend_Controller_Router_Default', $options);
        $this->_model->setFront($this->_frontMock);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testRouterCannotProcessRequestsWithWrongFrontName()
    {
        $request = $this->getMock('Mage_Core_Controller_Request_Http');
        $request->expects($this->once())
            ->method('getPathInfo')
            ->will($this->returnValue('frontend/admin/dashboard'));
        $this->_frontMock->expects($this->never())
            ->method('setDefault');
        $this->_model->match($request);
    }

    public function testRouterCanProcessRequestsWithProperFrontName()
    {
        $request = $this->getMock('Mage_Core_Controller_Request_Http');
        $request->expects($this->once())
            ->method('getPathInfo')
            ->will($this->returnValue('backend/admin/dashboard'));
        $this->_frontMock->expects($this->once())
            ->method('setDefault');
        $this->_model->match($request);
    }


    /**
     * @covers Mage_Backend_Controller_Router_Default::collectRoutes
     */
    public function testCollectRoutes()
    {
        $this->_model->collectRoutes(Mage::helper('Mage_Backend_Helper_Data')->getAreaCode(), 'admin');
        $this->assertEquals(
            'admin',
            $this->_model->getFrontNameByRoute('adminhtml')
        );
    }

    /**
     * @covers Mage_Backend_Controller_Router_Default::fetchDefault
     */
    public function testFetchDefault()
    {
        $default = array(
            'area' => '',
            'module' => 'admin',
            'controller' => 'index',
            'action' => 'index'
        );
        $this->_frontMock->expects($this->once())
            ->method('setDefault')
            ->with($this->equalTo($default));
        $this->_model->fetchDefault();
    }

    /**
     * @param string $module
     * @param string $controller
     * @param string $fileName
     *
     * @covers Mage_Backend_Controller_Router_Default::getControllerFileName
     * @dataProvider getControllerFileNameDataProvider
     */
    public function testGetControllerFileName($module, $controller, $fileName)
    {
        $file = $this->_model->getControllerFileName($module, $controller);
        $this->assertStringEndsWith($fileName, $file);
    }

    public function getControllerFileNameDataProvider()
    {
        return array(
            array('Mage_Adminhtml', 'index', 'Adminhtml' . DS . 'controllers' . DS . 'IndexController.php'),
            array(
                'Mage_Index',
                'process',
                'Index' . DS . 'controllers' . DS . 'Adminhtml' . DS . 'ProcessController.php'
            ),
            array(
                'Mage_Index_Adminhtml',
                'process',
                'Index' . DS . 'controllers' . DS . 'Adminhtml' . DS . 'ProcessController.php'
            ),
        );
    }

    /**
     * @param string $module
     * @param string $controller
     * @param string $className
     *
     * @covers Mage_Backend_Controller_Router_Default::getControllerClassName
     * @dataProvider getControllerClassNameDataProvider
     */
    public function testGetControllerClassName($module, $controller, $className)
    {
        $this->assertEquals($className, $this->_model->getControllerClassName($module, $controller));
    }

    public function getControllerClassNameDataProvider()
    {
        return array(
            array('Mage_Adminhtml', 'index', 'Mage_Adminhtml_IndexController'),
            array('Mage_Index', 'process', 'Mage_Index_Adminhtml_ProcessController'),
            array('Mage_Index_Adminhtml', 'process', 'Mage_Index_Adminhtml_ProcessController'),
        );
    }
}
