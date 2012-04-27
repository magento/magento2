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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Controller_Router_DefaultTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Controller_Router_Default
     */
    protected $_model;

    protected function setUp()
    {
        $options = array(
            'area' => 'adminhtml',
            'base_controller' => 'Mage_Backend_Controller_ActionAbstract'
        );
        $this->_model = new Mage_Backend_Controller_Router_Default($options);
        $this->_model->setFront(Mage::app()->getFrontController());
    }

    /**
     * @covers Mage_Backend_Controller_Router_Default::collectRoutes
     */
    public function testCollectRoutes()
    {
        $this->_model->collectRoutes('admin', 'admin');
        $this->assertEquals('admin', $this->_model->getFrontNameByRoute('adminhtml'));
    }

    /**
     * @covers Mage_Backend_Controller_Router_Default::fetchDefault
     */
    public function testFetchDefault()
    {
        $default = array(
            'module' => '',
            'controller' => 'index',
            'action' => 'index'
        );
        $this->_model->fetchDefault();
        $this->assertEquals($default, Mage::app()->getFrontController()->getDefault());
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
