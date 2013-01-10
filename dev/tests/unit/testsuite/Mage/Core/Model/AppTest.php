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
    /*
     * Test layout class instance
     */
    const LAYOUT_INSTANCE = 'TestLayoutInstance';

    /**
     * @var Mage_Core_Model_App
     */
    protected $_model;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    public function setUp()
    {
        $frontController = $this->getMock('Mage_Core_Controller_Varien_Front', array(), array(), '', false);
        $this->_objectManager = $this->getMock('Magento_ObjectManager_Zend', array('get'), array(), '', false);
        $this->_model = new Mage_Core_Model_App($frontController, $this->_objectManager);
    }

    public function testGetLayout()
    {
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with('Mage_Core_Model_Layout')
            ->will($this->returnValue(self::LAYOUT_INSTANCE));

        $this->assertEquals(self::LAYOUT_INSTANCE, $this->_model->getLayout());
    }
}
