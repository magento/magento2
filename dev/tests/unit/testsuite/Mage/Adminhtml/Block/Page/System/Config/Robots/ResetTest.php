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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Adminhtml_Block_Page_System_Config_Robots_Reset
 */
class Mage_Adminhtml_Block_Page_System_Config_Robots_ResetTest extends Magento_Test_TestCase_ObjectManagerAbstract
{
    /**
     * @var Mage_Adminhtml_Block_Page_System_Config_Robots_Reset
     */
    private $_resetRobotsBlock;

    /**
     * @var Mage_Page_Helper_Robots|PHPUnit_Framework_MockObject_MockObject
     */
    private $_mockRobotsHelper;

    protected function setUp()
    {
        $this->_resetRobotsBlock = $this->getBlock('Mage_Adminhtml_Block_Page_System_Config_Robots_Reset');
        $this->_mockRobotsHelper = $this->getMockBuilder('Mage_Page_Helper_Robots')
            ->setMethods(array('getRobotsDefaultCustomInstructions'))
            ->getMock();
        Mage::register('_helper/Mage_Page_Helper_Robots', $this->_mockRobotsHelper);
    }

    protected function tearDown()
    {
        Mage::unregister('_helper/Mage_Page_Helper_Robots');
    }

    /**
     * @covers Mage_Adminhtml_Block_Page_System_Config_Robots_Reset::getRobotsDefaultCustomInstructions
     */
    public function testGetRobotsDefaultCustomInstructions()
    {
        $expectedInstructions = 'User-agent: *';
        $this->_mockRobotsHelper
            ->expects($this->once())
            ->method('getRobotsDefaultCustomInstructions')
            ->will($this->returnValue($expectedInstructions));
        $this->assertEquals($expectedInstructions, $this->_resetRobotsBlock->getRobotsDefaultCustomInstructions());
    }
}
