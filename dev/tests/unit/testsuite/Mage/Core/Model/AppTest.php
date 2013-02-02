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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_App
 */
class Mage_Core_Model_AppTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_App|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $frontController = $this->getMock('Mage_Core_Controller_Varien_Front', array(), array(), '', false);

        $this->_objectManager = new Magento_ObjectManager_Zend();
        $dirs = new Mage_Core_Model_Dir(__DIR__, array(), array(Mage_Core_Model_Dir::CONFIG => __DIR__));
        $this->_objectManager->addSharedInstance($dirs, 'Mage_Core_Model_Dir');

        $this->_model = $this->getMock(
            'Mage_Core_Model_App',
            array('_initEnvironment', '_initFilesystem', '_initLogger', '_initCache'),
            array($frontController, $this->_objectManager)
        );
        $this->_objectManager->addSharedInstance($this->_model, 'Mage_Core_Model_App');
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_objectManager = null;
    }

    public function testIsInstalledFalse()
    {
        $this->_model->baseInit(array(
            Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA
                => sprintf(Mage_Core_Model_Config::CONFIG_TEMPLATE_INSTALL_DATE, 'invalid')
        ));
        $this->assertFalse($this->_model->isInstalled());
    }

    public function testIsInstalledTrue()
    {
        $this->_model->baseInit(array(
            Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA
                => sprintf(Mage_Core_Model_Config::CONFIG_TEMPLATE_INSTALL_DATE, 'Fri, 28 Dec 2012 11:29:51 -0800')
        ));
        $this->assertTrue($this->_model->isInstalled());
    }

    /**
     * @expectedException Magento_Exception
     * @expectedExceptionMessage Application is not installed yet, please complete the installation first.
     */
    public function testRequireInstalledInstance()
    {
        $this->_model->baseInit(array(
            Mage_Core_Model_Config::INIT_OPTION_EXTRA_DATA
                => sprintf(Mage_Core_Model_Config::CONFIG_TEMPLATE_INSTALL_DATE, 'invalid')
        ));
        $this->_model->requireInstalledInstance();
    }

    public function testGetLayout()
    {
        $layout = $this->getMock('Mage_Core_Model_Layout', array(), array(), '', false);
        $this->_objectManager->addSharedInstance($layout, 'Mage_Core_Model_Layout');

        $this->assertEquals($layout, $this->_model->getLayout());
    }
}
