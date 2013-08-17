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
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoAppArea adminhtml
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_View_AccordionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Adminhtml_Block_Customer_Edit_Tab_View_Accordion
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('Mage_Customer_Model_Customer');
        $customer->load(1);
        Mage::register('current_customer', $customer);
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout', array('area' => Mage_Core_Model_App_Area::AREA_ADMINHTML));
        $this->_block = $layout->createBlock('Mage_Adminhtml_Block_Customer_Edit_Tab_View_Accordion');
    }

    /**
     * magentoDataFixture Mage/Customer/_files/customer.php
     */
    public function testToHtml()
    {
        $this->assertContains('Wishlist - 0 item(s)', $this->_block->toHtml());
    }
}
