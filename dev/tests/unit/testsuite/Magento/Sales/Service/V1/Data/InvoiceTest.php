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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Service\V1\Data;

/**
 * Class InvoiceTest
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @package Magento\Sales\Service\V1\Data
 */
class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBaseCurrencyCode()
    {
        $data = ['base_currency_code' => 'test_value_base_currency_code'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_currency_code', $object->getBaseCurrencyCode());
    }

    public function testGetBaseDiscountAmount()
    {
        $data = ['base_discount_amount' => 'test_value_base_discount_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_discount_amount', $object->getBaseDiscountAmount());
    }

    public function testGetBaseGrandTotal()
    {
        $data = ['base_grand_total' => 'test_value_base_grand_total'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_grand_total', $object->getBaseGrandTotal());
    }

    public function testGetBaseHiddenTaxAmount()
    {
        $data = ['base_hidden_tax_amount' => 'test_value_base_hidden_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_hidden_tax_amount', $object->getBaseHiddenTaxAmount());
    }

    public function testGetBaseShippingAmount()
    {
        $data = ['base_shipping_amount' => 'test_value_base_shipping_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_amount', $object->getBaseShippingAmount());
    }

    public function testGetBaseShippingHiddenTaxAmnt()
    {
        $data = ['base_shipping_hidden_tax_amnt' => 'test_value_base_shipping_hidden_tax_amnt'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_hidden_tax_amnt', $object->getBaseShippingHiddenTaxAmnt());
    }

    public function testGetBaseShippingInclTax()
    {
        $data = ['base_shipping_incl_tax' => 'test_value_base_shipping_incl_tax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_incl_tax', $object->getBaseShippingInclTax());
    }

    public function testGetBaseShippingTaxAmount()
    {
        $data = ['base_shipping_tax_amount' => 'test_value_base_shipping_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_tax_amount', $object->getBaseShippingTaxAmount());
    }

    public function testGetBaseSubtotal()
    {
        $data = ['base_subtotal' => 'test_value_base_subtotal'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_subtotal', $object->getBaseSubtotal());
    }

    public function testGetBaseSubtotalInclTax()
    {
        $data = ['base_subtotal_incl_tax' => 'test_value_base_subtotal_incl_tax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_subtotal_incl_tax', $object->getBaseSubtotalInclTax());
    }

    public function testGetBaseTaxAmount()
    {
        $data = ['base_tax_amount' => 'test_value_base_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_amount', $object->getBaseTaxAmount());
    }

    public function testGetBaseTotalRefunded()
    {
        $data = ['base_total_refunded' => 'test_value_base_total_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_refunded', $object->getBaseTotalRefunded());
    }

    public function testGetBaseToGlobalRate()
    {
        $data = ['base_to_global_rate' => 'test_value_base_to_global_rate'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_to_global_rate', $object->getBaseToGlobalRate());
    }

    public function testGetBaseToOrderRate()
    {
        $data = ['base_to_order_rate' => 'test_value_base_to_order_rate'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_base_to_order_rate', $object->getBaseToOrderRate());
    }

    public function testGetBillingAddressId()
    {
        $data = ['billing_address_id' => 'test_value_billing_address_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_billing_address_id', $object->getBillingAddressId());
    }

    public function testGetCanVoidFlag()
    {
        $data = ['can_void_flag' => 'test_value_can_void_flag'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_can_void_flag', $object->getCanVoidFlag());
    }

    public function testGetCreatedAt()
    {
        $data = ['created_at' => 'test_value_created_at'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_created_at', $object->getCreatedAt());
    }

    public function testGetDiscountAmount()
    {
        $data = ['discount_amount' => 'test_value_discount_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_discount_amount', $object->getDiscountAmount());
    }

    public function testGetDiscountDescription()
    {
        $data = ['discount_description' => 'test_value_discount_description'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_discount_description', $object->getDiscountDescription());
    }

    public function testGetEmailSent()
    {
        $data = ['email_sent' => 'test_value_email_sent'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_email_sent', $object->getEmailSent());
    }

    public function testGetEntityId()
    {
        $data = ['entity_id' => 'test_value_entity_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_entity_id', $object->getEntityId());
    }

    public function testGetGlobalCurrencyCode()
    {
        $data = ['global_currency_code' => 'test_value_global_currency_code'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_global_currency_code', $object->getGlobalCurrencyCode());
    }

    public function testGetGrandTotal()
    {
        $data = ['grand_total' => 'test_value_grand_total'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_grand_total', $object->getGrandTotal());
    }

    public function testGetHiddenTaxAmount()
    {
        $data = ['hidden_tax_amount' => 'test_value_hidden_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_hidden_tax_amount', $object->getHiddenTaxAmount());
    }

    public function testGetIncrementId()
    {
        $data = ['increment_id' => 'test_value_increment_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_increment_id', $object->getIncrementId());
    }

    public function testGetIsUsedForRefund()
    {
        $data = ['is_used_for_refund' => 'test_value_is_used_for_refund'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_is_used_for_refund', $object->getIsUsedForRefund());
    }

    public function testGetOrderCurrencyCode()
    {
        $data = ['order_currency_code' => 'test_value_order_currency_code'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_order_currency_code', $object->getOrderCurrencyCode());
    }

    public function testGetOrderId()
    {
        $data = ['order_id' => 'test_value_order_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_order_id', $object->getOrderId());
    }

    public function testGetShippingAddressId()
    {
        $data = ['shipping_address_id' => 'test_value_shipping_address_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_address_id', $object->getShippingAddressId());
    }

    public function testGetShippingAmount()
    {
        $data = ['shipping_amount' => 'test_value_shipping_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_amount', $object->getShippingAmount());
    }

    public function testGetShippingHiddenTaxAmount()
    {
        $data = ['shipping_hidden_tax_amount' => 'test_value_shipping_hidden_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_hidden_tax_amount', $object->getShippingHiddenTaxAmount());
    }

    public function testGetShippingInclTax()
    {
        $data = ['shipping_incl_tax' => 'test_value_shipping_incl_tax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_incl_tax', $object->getShippingInclTax());
    }

    public function testGetShippingTaxAmount()
    {
        $data = ['shipping_tax_amount' => 'test_value_shipping_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_tax_amount', $object->getShippingTaxAmount());
    }

    public function testGetState()
    {
        $data = ['state' => 'test_value_state'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_state', $object->getState());
    }

    public function testGetStoreCurrencyCode()
    {
        $data = ['store_currency_code' => 'test_value_store_currency_code'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_store_currency_code', $object->getStoreCurrencyCode());
    }

    public function testGetStoreId()
    {
        $data = ['store_id' => 'test_value_store_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_store_id', $object->getStoreId());
    }

    public function testGetStoreToBaseRate()
    {
        $data = ['store_to_base_rate' => 'test_value_store_to_base_rate'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_store_to_base_rate', $object->getStoreToBaseRate());
    }

    public function testGetStoreToOrderRate()
    {
        $data = ['store_to_order_rate' => 'test_value_store_to_order_rate'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_store_to_order_rate', $object->getStoreToOrderRate());
    }

    public function testGetSubtotal()
    {
        $data = ['subtotal' => 'test_value_subtotal'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_subtotal', $object->getSubtotal());
    }

    public function testGetSubtotalInclTax()
    {
        $data = ['subtotal_incl_tax' => 'test_value_subtotal_incl_tax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_subtotal_incl_tax', $object->getSubtotalInclTax());
    }

    public function testGetTaxAmount()
    {
        $data = ['tax_amount' => 'test_value_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_tax_amount', $object->getTaxAmount());
    }

    public function testGetTotalQty()
    {
        $data = ['total_qty' => 'test_value_total_qty'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_total_qty', $object->getTotalQty());
    }

    public function testGetTransactionId()
    {
        $data = ['transaction_id' => 'test_value_transaction_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_transaction_id', $object->getTransactionId());
    }

    public function testGetUpdatedAt()
    {
        $data = ['updated_at' => 'test_value_updated_at'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals('test_value_updated_at', $object->getUpdatedAt());
    }

    public function testGetItems()
    {
        $data = ['items' => 'test_value_items'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Invoice($abstractBuilderMock);

        $this->assertEquals(['test_value_items'], $object->getItems());
    }
}
