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
 * @package     Mage_Payment
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Payment_Block_Info_Instructions
 */
class Mage_Payment_Block_Info_InstructionsTest extends Magento_Test_TestCase_ObjectManagerAbstract
{
    /**
     * @var Varien_Object
     */
    protected $_method;

    /**
     * @var Mage_Payment_Model_Info
     */
    protected $_info;

    /**
     * @var Mage_Payment_Block_Info_Instructions
     */
    protected $_instructions;

    protected function setUp()
    {
        $this->_method = new Varien_Object();
        $this->_info = $this->getModel('Mage_Payment_Model_Info');
        $this->_instructions = $this->getBlock('Mage_Payment_Block_Info_Instructions');

        $this->_info->setMethodInstance($this->_method);
        $this->_instructions->setInfo($this->_info);
    }

    public function testGetInstructionsSetInstructions()
    {
        $this->assertNull($this->_instructions->getInstructions());
        $testInstruction = 'first test';
        $this->_method->setInstructions($testInstruction);
        $this->assertEquals($testInstruction, $this->_instructions->getInstructions());
    }

    public function testGetInstructionsSetInformation()
    {
        $this->assertNull($this->_instructions->getInstructions());
        $testInstruction = 'second test';
        $this->_info->setAdditionalInformation('instructions', $testInstruction);
        $this->assertEquals($testInstruction, $this->_instructions->getInstructions());
    }
}
