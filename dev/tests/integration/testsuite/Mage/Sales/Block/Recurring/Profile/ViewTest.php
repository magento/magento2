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
 * @package     Mage_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Sales
 */
class Mage_Sales_Block_Recurring_Profile_ViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Sales_Block_Recurring_Profile_View
     */
    protected $_block;

    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @var Mage_Sales_Model_Recurring_Profile
     */
    protected $_profile;

    public function setUp()
    {
        $this->_profile = new Mage_Sales_Model_Recurring_Profile;
        Mage::register('current_recurring_profile', $this->_profile);

        $this->_block = new Mage_Sales_Block_Recurring_Profile_View;
        $this->_layout = new Mage_Core_Model_Layout;
        $this->_layout->addBlock($this->_block, 'block');
    }

    public function tearDown()
    {
        Mage::unregister('current_recurring_profile');
    }

    public function testPrepareAddressInfo()
    {
        $this->_profile->setData('billing_address_info', array('city' => 'Los Angeles'));
        $this->_block->prepareAddressInfo();
        $info = $this->_block->getRenderedInfo();
        $this->assertContains('Los Angeles', $info[0]->getValue());
    }

    public function testToHtmlPropagatesUrl()
    {
        $this->_block->setShouldPrepareInfoTabs(true);
        $child1 = $this->_layout->addBlock('Mage_Core_Block_Text', 'child1', 'block');
        $this->_layout->addToParentGroup('child1', 'info_tabs');
        $child2 = $this->_layout->addBlock('Mage_Core_Block_Text', 'child2', 'block');
        $this->_layout->addToParentGroup('child2', 'info_tabs');

        $this->assertEmpty($child1->getViewUrl());
        $this->assertEmpty($child2->getViewUrl());
        $this->_block->toHtml();
        $this->assertNotEmpty($child1->getViewUrl());
        $this->assertNotEmpty($child2->getViewUrl());
    }
}
