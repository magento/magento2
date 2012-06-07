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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Sales_OrderControllerTest extends Mage_Adminhtml_Utility_Controller
{
    public function testIndexAction()
    {
        $this->dispatch('admin/sales_order/index');
        $this->assertContains('Total 0 records found', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Mage/Sales/_files/order.php
     */
    public function testIndexActionWithOrder()
    {
        $this->dispatch('admin/sales_order/index');
        $this->assertContains('Total 1 records found', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Mage/Sales/_files/order.php
     */
    public function testOrderViewAction()
    {
        $order = new Mage_Sales_Model_Order;
        $order->load('100000001', 'increment_id');
        $this->dispatch('admin/sales_order/view/order_id/' . $order->getId());
        $this->assertContains('Los Angeles', $this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Mage/Adminhtml/controllers/Sales/_files/address.php
     */
    public function testAddressActionNoVAT()
    {
        $address = new Mage_Sales_Model_Order_Address;
        $address->load('a_unique_firstname', 'firstname');
        $this->getRequest()->setParam('address_id', $address->getId());
        $this->dispatch('admin/sales_order/address');
        $html = $this->getResponse()->getBody();
        $prohibitedStrings = array('validate-vat', 'validateVat', 'Validate VAT');
        foreach ($prohibitedStrings as $string) {
            $this->assertNotContains($string, $html, 'VAT button must not be shown while editing address', true);
        }
    }
}
