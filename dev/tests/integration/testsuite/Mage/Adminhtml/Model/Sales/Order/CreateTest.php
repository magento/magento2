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

class Mage_Adminhtml_Model_Sales_Order_CreateTest extends PHPUnit_Framework_TestCase
{
    /**
     * Model instance
     *
     * @var Mage_Adminhtml_Model_Sales_Order_Create
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Adminhtml_Model_Sales_Order_Create();
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @magentoDataFixture Mage/Downloadable/_files/product.php
     * @magentoDataFixture Mage/Downloadable/_files/order_with_downloadable_product.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenEmpty()
    {
        $order = new Mage_Sales_Model_Order();
        $order->loadByIncrementId('100000001');
        $this->assertFalse($order->getShippingAddress());

        Mage::unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertFalse($order->getShippingAddress());
    }

    /**
     * @magentoDataFixture Mage/Downloadable/_files/product.php
     * @magentoDataFixture Mage/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDataFixture Mage/Adminhtml/_files/order_shipping_address_same_as_billing.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenSame()
    {
        $order = new Mage_Sales_Model_Order();
        $order->loadByIncrementId('100000001');

        $this->assertNull($order->getShippingAddress()->getSameAsBilling());

        Mage::unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertTrue($order->getShippingAddress()->getSameAsBilling());
    }

    /**
     * @magentoDataFixture Mage/Downloadable/_files/product.php
     * @magentoDataFixture Mage/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDataFixture Mage/Adminhtml/_files/order_shipping_address_different_to_billing.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenDifferent()
    {
        $order = new Mage_Sales_Model_Order();
        $order->loadByIncrementId('100000001');

        $this->assertNull($order->getShippingAddress()->getSameAsBilling());

        Mage::unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertFalse($order->getShippingAddress()->getSameAsBilling());
    }
}
