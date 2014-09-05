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
 * Class OrderTest
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @package Magento\Sales\Service\V1\Data
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAdjustmentNegative()
    {
        $data = ['adjustment_negative' => 'test_value_adjustment_negative'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_adjustment_negative', $object->getAdjustmentNegative());
    }

    public function testGetAdjustmentPositive()
    {
        $data = ['adjustment_positive' => 'test_value_adjustment_positive'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_adjustment_positive', $object->getAdjustmentPositive());
    }

    public function testGetAppliedRuleIds()
    {
        $data = ['applied_rule_ids' => 'test_value_applied_rule_ids'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_applied_rule_ids', $object->getAppliedRuleIds());
    }

    public function testGetBaseAdjustmentNegative()
    {
        $data = ['base_adjustment_negative' => 'test_value_base_adjustment_negative'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_adjustment_negative', $object->getBaseAdjustmentNegative());
    }

    public function testGetBaseAdjustmentPositive()
    {
        $data = ['base_adjustment_positive' => 'test_value_base_adjustment_positive'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_adjustment_positive', $object->getBaseAdjustmentPositive());
    }

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_discount_amount', $object->getBaseDiscountAmount());
    }

    public function testGetBaseDiscountCanceled()
    {
        $data = ['base_discount_canceled' => 'test_value_base_discount_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_discount_canceled', $object->getBaseDiscountCanceled());
    }

    public function testGetBaseDiscountInvoiced()
    {
        $data = ['base_discount_invoiced' => 'test_value_base_discount_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_discount_invoiced', $object->getBaseDiscountInvoiced());
    }

    public function testGetBaseDiscountRefunded()
    {
        $data = ['base_discount_refunded' => 'test_value_base_discount_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_discount_refunded', $object->getBaseDiscountRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_hidden_tax_amount', $object->getBaseHiddenTaxAmount());
    }

    public function testGetBaseHiddenTaxInvoiced()
    {
        $data = ['base_hidden_tax_invoiced' => 'test_value_base_hidden_tax_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_hidden_tax_invoiced', $object->getBaseHiddenTaxInvoiced());
    }

    public function testGetBaseHiddenTaxRefunded()
    {
        $data = ['base_hidden_tax_refunded' => 'test_value_base_hidden_tax_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_hidden_tax_refunded', $object->getBaseHiddenTaxRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_amount', $object->getBaseShippingAmount());
    }

    public function testGetBaseShippingCanceled()
    {
        $data = ['base_shipping_canceled' => 'test_value_base_shipping_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_canceled', $object->getBaseShippingCanceled());
    }

    public function testGetBaseShippingDiscountAmount()
    {
        $data = ['base_shipping_discount_amount' => 'test_value_base_shipping_discount_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_discount_amount', $object->getBaseShippingDiscountAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_incl_tax', $object->getBaseShippingInclTax());
    }

    public function testGetBaseShippingInvoiced()
    {
        $data = ['base_shipping_invoiced' => 'test_value_base_shipping_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_invoiced', $object->getBaseShippingInvoiced());
    }

    public function testGetBaseShippingRefunded()
    {
        $data = ['base_shipping_refunded' => 'test_value_base_shipping_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_refunded', $object->getBaseShippingRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_tax_amount', $object->getBaseShippingTaxAmount());
    }

    public function testGetBaseShippingTaxRefunded()
    {
        $data = ['base_shipping_tax_refunded' => 'test_value_base_shipping_tax_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_tax_refunded', $object->getBaseShippingTaxRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_subtotal', $object->getBaseSubtotal());
    }

    public function testGetBaseSubtotalCanceled()
    {
        $data = ['base_subtotal_canceled' => 'test_value_base_subtotal_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_subtotal_canceled', $object->getBaseSubtotalCanceled());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_subtotal_incl_tax', $object->getBaseSubtotalInclTax());
    }

    public function testGetBaseSubtotalInvoiced()
    {
        $data = ['base_subtotal_invoiced' => 'test_value_base_subtotal_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_subtotal_invoiced', $object->getBaseSubtotalInvoiced());
    }

    public function testGetBaseSubtotalRefunded()
    {
        $data = ['base_subtotal_refunded' => 'test_value_base_subtotal_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_subtotal_refunded', $object->getBaseSubtotalRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_amount', $object->getBaseTaxAmount());
    }

    public function testGetBaseTaxCanceled()
    {
        $data = ['base_tax_canceled' => 'test_value_base_tax_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_canceled', $object->getBaseTaxCanceled());
    }

    public function testGetBaseTaxInvoiced()
    {
        $data = ['base_tax_invoiced' => 'test_value_base_tax_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_invoiced', $object->getBaseTaxInvoiced());
    }

    public function testGetBaseTaxRefunded()
    {
        $data = ['base_tax_refunded' => 'test_value_base_tax_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_refunded', $object->getBaseTaxRefunded());
    }

    public function testGetBaseTotalCanceled()
    {
        $data = ['base_total_canceled' => 'test_value_base_total_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_canceled', $object->getBaseTotalCanceled());
    }

    public function testGetBaseTotalDue()
    {
        $data = ['base_total_due' => 'test_value_base_total_due'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_due', $object->getBaseTotalDue());
    }

    public function testGetBaseTotalInvoiced()
    {
        $data = ['base_total_invoiced' => 'test_value_base_total_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_invoiced', $object->getBaseTotalInvoiced());
    }

    public function testGetBaseTotalInvoicedCost()
    {
        $data = ['base_total_invoiced_cost' => 'test_value_base_total_invoiced_cost'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_invoiced_cost', $object->getBaseTotalInvoicedCost());
    }

    public function testGetBaseTotalOfflineRefunded()
    {
        $data = ['base_total_offline_refunded' => 'test_value_base_total_offline_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_offline_refunded', $object->getBaseTotalOfflineRefunded());
    }

    public function testGetBaseTotalOnlineRefunded()
    {
        $data = ['base_total_online_refunded' => 'test_value_base_total_online_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_online_refunded', $object->getBaseTotalOnlineRefunded());
    }

    public function testGetBaseTotalPaid()
    {
        $data = ['base_total_paid' => 'test_value_base_total_paid'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_paid', $object->getBaseTotalPaid());
    }

    public function testGetBaseTotalQtyOrdered()
    {
        $data = ['base_total_qty_ordered' => 'test_value_base_total_qty_ordered'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_base_total_qty_ordered', $object->getBaseTotalQtyOrdered());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_billing_address_id', $object->getBillingAddressId());
    }

    public function testGetCanShipPartially()
    {
        $data = ['can_ship_partially' => 'test_value_can_ship_partially'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_can_ship_partially', $object->getCanShipPartially());
    }

    public function testGetCanShipPartiallyItem()
    {
        $data = ['can_ship_partially_item' => 'test_value_can_ship_partially_item'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_can_ship_partially_item', $object->getCanShipPartiallyItem());
    }

    public function testGetCouponCode()
    {
        $data = ['coupon_code' => 'test_value_coupon_code'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_coupon_code', $object->getCouponCode());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_created_at', $object->getCreatedAt());
    }

    public function testGetCustomerDob()
    {
        $data = ['customer_dob' => 'test_value_customer_dob'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_dob', $object->getCustomerDob());
    }

    public function testGetCustomerEmail()
    {
        $data = ['customer_email' => 'test_value_customer_email'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_email', $object->getCustomerEmail());
    }

    public function testGetCustomerFirstname()
    {
        $data = ['customer_firstname' => 'test_value_customer_firstname'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_firstname', $object->getCustomerFirstname());
    }

    public function testGetCustomerGender()
    {
        $data = ['customer_gender' => 'test_value_customer_gender'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_gender', $object->getCustomerGender());
    }

    public function testGetCustomerGroupId()
    {
        $data = ['customer_group_id' => 'test_value_customer_group_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_group_id', $object->getCustomerGroupId());
    }

    public function testGetCustomerId()
    {
        $data = ['customer_id' => 'test_value_customer_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_id', $object->getCustomerId());
    }

    public function testGetCustomerIsGuest()
    {
        $data = ['customer_is_guest' => 'test_value_customer_is_guest'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_is_guest', $object->getCustomerIsGuest());
    }

    public function testGetCustomerLastname()
    {
        $data = ['customer_lastname' => 'test_value_customer_lastname'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_lastname', $object->getCustomerLastname());
    }

    public function testGetCustomerMiddlename()
    {
        $data = ['customer_middlename' => 'test_value_customer_middlename'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_middlename', $object->getCustomerMiddlename());
    }

    public function testGetCustomerNote()
    {
        $data = ['customer_note' => 'test_value_customer_note'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_note', $object->getCustomerNote());
    }

    public function testGetCustomerNoteNotify()
    {
        $data = ['customer_note_notify' => 'test_value_customer_note_notify'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_note_notify', $object->getCustomerNoteNotify());
    }

    public function testGetCustomerPrefix()
    {
        $data = ['customer_prefix' => 'test_value_customer_prefix'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_prefix', $object->getCustomerPrefix());
    }

    public function testGetCustomerSuffix()
    {
        $data = ['customer_suffix' => 'test_value_customer_suffix'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_suffix', $object->getCustomerSuffix());
    }

    public function testGetCustomerTaxvat()
    {
        $data = ['customer_taxvat' => 'test_value_customer_taxvat'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_customer_taxvat', $object->getCustomerTaxvat());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_discount_amount', $object->getDiscountAmount());
    }

    public function testGetDiscountCanceled()
    {
        $data = ['discount_canceled' => 'test_value_discount_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_discount_canceled', $object->getDiscountCanceled());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_discount_description', $object->getDiscountDescription());
    }

    public function testGetDiscountInvoiced()
    {
        $data = ['discount_invoiced' => 'test_value_discount_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_discount_invoiced', $object->getDiscountInvoiced());
    }

    public function testGetDiscountRefunded()
    {
        $data = ['discount_refunded' => 'test_value_discount_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_discount_refunded', $object->getDiscountRefunded());
    }

    public function testGetEditIncrement()
    {
        $data = ['edit_increment' => 'test_value_edit_increment'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_edit_increment', $object->getEditIncrement());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_entity_id', $object->getEntityId());
    }

    public function testGetExtCustomerId()
    {
        $data = ['ext_customer_id' => 'test_value_ext_customer_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_ext_customer_id', $object->getExtCustomerId());
    }

    public function testGetExtOrderId()
    {
        $data = ['ext_order_id' => 'test_value_ext_order_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_ext_order_id', $object->getExtOrderId());
    }

    public function testGetForcedShipmentWithInvoice()
    {
        $data = ['forced_shipment_with_invoice' => 'test_value_forced_shipment_with_invoice'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_forced_shipment_with_invoice', $object->getForcedShipmentWithInvoice());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_hidden_tax_amount', $object->getHiddenTaxAmount());
    }

    public function testGetHiddenTaxInvoiced()
    {
        $data = ['hidden_tax_invoiced' => 'test_value_hidden_tax_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_hidden_tax_invoiced', $object->getHiddenTaxInvoiced());
    }

    public function testGetHiddenTaxRefunded()
    {
        $data = ['hidden_tax_refunded' => 'test_value_hidden_tax_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_hidden_tax_refunded', $object->getHiddenTaxRefunded());
    }

    public function testGetHoldBeforeState()
    {
        $data = ['hold_before_state' => 'test_value_hold_before_state'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_hold_before_state', $object->getHoldBeforeState());
    }

    public function testGetHoldBeforeStatus()
    {
        $data = ['hold_before_status' => 'test_value_hold_before_status'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_hold_before_status', $object->getHoldBeforeStatus());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_increment_id', $object->getIncrementId());
    }

    public function testGetIsVirtual()
    {
        $data = ['is_virtual' => 'test_value_is_virtual'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_is_virtual', $object->getIsVirtual());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_order_currency_code', $object->getOrderCurrencyCode());
    }

    public function testGetOriginalIncrementId()
    {
        $data = ['original_increment_id' => 'test_value_original_increment_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_original_increment_id', $object->getOriginalIncrementId());
    }

    public function testGetPaymentAuthorizationAmount()
    {
        $data = ['payment_authorization_amount' => 'test_value_payment_authorization_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_payment_authorization_amount', $object->getPaymentAuthorizationAmount());
    }

    public function testGetPaymentAuthExpiration()
    {
        $data = ['payment_auth_expiration' => 'test_value_payment_auth_expiration'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_payment_auth_expiration', $object->getPaymentAuthExpiration());
    }

    public function testGetProtectCode()
    {
        $data = ['protect_code' => 'test_value_protect_code'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_protect_code', $object->getProtectCode());
    }

    public function testGetQuoteAddressId()
    {
        $data = ['quote_address_id' => 'test_value_quote_address_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_quote_address_id', $object->getQuoteAddressId());
    }

    public function testGetQuoteId()
    {
        $data = ['quote_id' => 'test_value_quote_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_quote_id', $object->getQuoteId());
    }

    public function testGetRelationChildId()
    {
        $data = ['relation_child_id' => 'test_value_relation_child_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_relation_child_id', $object->getRelationChildId());
    }

    public function testGetRelationChildRealId()
    {
        $data = ['relation_child_real_id' => 'test_value_relation_child_real_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_relation_child_real_id', $object->getRelationChildRealId());
    }

    public function testGetRelationParentId()
    {
        $data = ['relation_parent_id' => 'test_value_relation_parent_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_relation_parent_id', $object->getRelationParentId());
    }

    public function testGetRelationParentRealId()
    {
        $data = ['relation_parent_real_id' => 'test_value_relation_parent_real_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_relation_parent_real_id', $object->getRelationParentRealId());
    }

    public function testGetRemoteIp()
    {
        $data = ['remote_ip' => 'test_value_remote_ip'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_remote_ip', $object->getRemoteIp());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_amount', $object->getShippingAmount());
    }

    public function testGetShippingCanceled()
    {
        $data = ['shipping_canceled' => 'test_value_shipping_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_canceled', $object->getShippingCanceled());
    }

    public function testGetShippingDescription()
    {
        $data = ['shipping_description' => 'test_value_shipping_description'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_description', $object->getShippingDescription());
    }

    public function testGetShippingDiscountAmount()
    {
        $data = ['shipping_discount_amount' => 'test_value_shipping_discount_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_discount_amount', $object->getShippingDiscountAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_incl_tax', $object->getShippingInclTax());
    }

    public function testGetShippingInvoiced()
    {
        $data = ['shipping_invoiced' => 'test_value_shipping_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_invoiced', $object->getShippingInvoiced());
    }

    public function testGetShippingMethod()
    {
        $data = ['shipping_method' => 'test_value_shipping_method'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_method', $object->getShippingMethod());
    }

    public function testGetShippingRefunded()
    {
        $data = ['shipping_refunded' => 'test_value_shipping_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_refunded', $object->getShippingRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_tax_amount', $object->getShippingTaxAmount());
    }

    public function testGetShippingTaxRefunded()
    {
        $data = ['shipping_tax_refunded' => 'test_value_shipping_tax_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_tax_refunded', $object->getShippingTaxRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_state', $object->getState());
    }

    public function testGetStatus()
    {
        $data = ['status' => 'test_value_status'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_status', $object->getStatus());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_store_id', $object->getStoreId());
    }

    public function testGetStoreName()
    {
        $data = ['store_name' => 'test_value_store_name'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_store_name', $object->getStoreName());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_subtotal', $object->getSubtotal());
    }

    public function testGetSubtotalCanceled()
    {
        $data = ['subtotal_canceled' => 'test_value_subtotal_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_subtotal_canceled', $object->getSubtotalCanceled());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_subtotal_incl_tax', $object->getSubtotalInclTax());
    }

    public function testGetSubtotalInvoiced()
    {
        $data = ['subtotal_invoiced' => 'test_value_subtotal_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_subtotal_invoiced', $object->getSubtotalInvoiced());
    }

    public function testGetSubtotalRefunded()
    {
        $data = ['subtotal_refunded' => 'test_value_subtotal_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_subtotal_refunded', $object->getSubtotalRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_tax_amount', $object->getTaxAmount());
    }

    public function testGetTaxCanceled()
    {
        $data = ['tax_canceled' => 'test_value_tax_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_tax_canceled', $object->getTaxCanceled());
    }

    public function testGetTaxInvoiced()
    {
        $data = ['tax_invoiced' => 'test_value_tax_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_tax_invoiced', $object->getTaxInvoiced());
    }

    public function testGetTaxRefunded()
    {
        $data = ['tax_refunded' => 'test_value_tax_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_tax_refunded', $object->getTaxRefunded());
    }

    public function testGetTotalCanceled()
    {
        $data = ['total_canceled' => 'test_value_total_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_canceled', $object->getTotalCanceled());
    }

    public function testGetTotalDue()
    {
        $data = ['total_due' => 'test_value_total_due'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_due', $object->getTotalDue());
    }

    public function testGetTotalInvoiced()
    {
        $data = ['total_invoiced' => 'test_value_total_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_invoiced', $object->getTotalInvoiced());
    }

    public function testGetTotalItemCount()
    {
        $data = ['total_item_count' => 'test_value_total_item_count'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_item_count', $object->getTotalItemCount());
    }

    public function testGetTotalOfflineRefunded()
    {
        $data = ['total_offline_refunded' => 'test_value_total_offline_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_offline_refunded', $object->getTotalOfflineRefunded());
    }

    public function testGetTotalOnlineRefunded()
    {
        $data = ['total_online_refunded' => 'test_value_total_online_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_online_refunded', $object->getTotalOnlineRefunded());
    }

    public function testGetTotalPaid()
    {
        $data = ['total_paid' => 'test_value_total_paid'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_paid', $object->getTotalPaid());
    }

    public function testGetTotalQtyOrdered()
    {
        $data = ['total_qty_ordered' => 'test_value_total_qty_ordered'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_qty_ordered', $object->getTotalQtyOrdered());
    }

    public function testGetTotalRefunded()
    {
        $data = ['total_refunded' => 'test_value_total_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_total_refunded', $object->getTotalRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_updated_at', $object->getUpdatedAt());
    }

    public function testGetWeight()
    {
        $data = ['weight' => 'test_value_weight'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_weight', $object->getWeight());
    }

    public function testGetXForwardedFor()
    {
        $data = ['x_forwarded_for' => 'test_value_x_forwarded_for'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_x_forwarded_for', $object->getXForwardedFor());
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

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_items', $object->getItems());
    }

    public function testGetBillingAddress()
    {
        $data = ['billing_address' => 'test_value_billing_address'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_billing_address', $object->getBillingAddress());
    }

    public function testGetShippingAddress()
    {
        $data = ['shipping_address' => 'test_value_shipping_address'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_address', $object->getShippingAddress());
    }

    public function testGetPayments()
    {
        $data = ['payments' => 'test_value_payments'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\Order($abstractBuilderMock);

        $this->assertEquals('test_value_payments', $object->getPayments());
    }
}
