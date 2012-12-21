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
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Controller_ActionTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var Mage_Adminhtml_Controller_Action|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass(
            'Mage_Adminhtml_Controller_Action',
            array(
                'request'         => new Magento_Test_Request(),
                'response'        => new Magento_Test_Response(),
                'areaCode'        => 'adminhtml',
                'objectManager'   => Mage::getObjectManager(),
                'frontController' => Mage::getObjectManager()->get('Mage_Core_Controller_Varien_Front'),
                'layoutFactory'   => Mage::getObjectManager()->get('Mage_Core_Model_Layout_Factory')
            )
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Mage_Backend_Controller_ActionAbstract', $this->_model);
    }

    /**
     * @covers  Mage_Adminhtml_Controller_Action::getUsedModuleName
     * @covers  Mage_Adminhtml_Controller_Action::setUsedModuleName
     */
    public function testUsedModuleName()
    {
        $this->assertEquals('adminhtml', $this->_model->getUsedModuleName());
        $this->_model->setUsedModuleName('dummy');
        $this->assertEquals('dummy', $this->_model->getUsedModuleName());
    }
}
