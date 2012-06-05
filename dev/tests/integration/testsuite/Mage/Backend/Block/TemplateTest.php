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

/**
 * Test class for Mage_Backend_Block_Template.
 *
 * @group module:Mage_Backend
 */
class Mage_Backend_Block_TemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_Template
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Mage_Backend_Block_Template;
    }

    /**
     * @covers Mage_Backend_Block_Template::getFormKey
     */
    public function testGetFormKey()
    {
        $this->assertGreaterThan(15, strlen($this->_block->getFormKey()));
    }

    /**
     * @covers Mage_Backend_Block_Template::isOutputEnabled
     */
    public function testIsOutputEnabled()
    {
        $this->_block->setData('module_name', 'dummy');
        Mage::app()->getStore()->setConfig('advanced/modules_disable_output/dummy', 'true');
        $this->assertFalse($this->_block->isOutputEnabled());

        Mage::app()->getStore()->setConfig('advanced/modules_disable_output/dummy', 'false');
        $this->assertTrue($this->_block->isOutputEnabled('dummy'));


    }
}
