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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * VDE toolbar buttons block
     *
     * @var Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstract
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = $this->getMockForAbstractClass('Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstract',
            array(), '', false
        );
    }

    public function testGetMode()
    {
        $this->_block->setMode(Mage_DesignEditor_Model_State::MODE_DESIGN);
        $this->assertEquals(Mage_DesignEditor_Model_State::MODE_DESIGN, $this->_block->getMode());
    }

    public function testSetMode()
    {
        $this->_block->setMode(Mage_DesignEditor_Model_State::MODE_NAVIGATION);
        $this->assertAttributeEquals(Mage_DesignEditor_Model_State::MODE_NAVIGATION, '_mode', $this->_block);
    }

    public function testIsDesignMode()
    {
        $this->_block->setMode(Mage_DesignEditor_Model_State::MODE_DESIGN);
        $this->assertTrue($this->_block->isDesignMode());

        $this->_block->setMode(Mage_DesignEditor_Model_State::MODE_NAVIGATION);
        $this->assertFalse($this->_block->isDesignMode());
    }

    public function testIsNavigationMode()
    {
        $this->_block->setMode(Mage_DesignEditor_Model_State::MODE_NAVIGATION);
        $this->assertTrue($this->_block->isNavigationMode());

        $this->_block->setMode(Mage_DesignEditor_Model_State::MODE_DESIGN);
        $this->assertFalse($this->_block->isNavigationMode());
    }
}
