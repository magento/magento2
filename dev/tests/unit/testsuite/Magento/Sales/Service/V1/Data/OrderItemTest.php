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
 * Class OrderItemTest
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @package Magento\Sales\Service\V1\Data
 */
class OrderItemTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAdditionalData()
    {
        $data = ['additional_data' => 'test_value_additional_data'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_additional_data', $object->getAdditionalData());
    }

    public function testGetAmountRefunded()
    {
        $data = ['amount_refunded' => 'test_value_amount_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_amount_refunded', $object->getAmountRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_applied_rule_ids', $object->getAppliedRuleIds());
    }

    public function testGetBaseAmountRefunded()
    {
        $data = ['base_amount_refunded' => 'test_value_base_amount_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_amount_refunded', $object->getBaseAmountRefunded());
    }

    public function testGetBaseCost()
    {
        $data = ['base_cost' => 'test_value_base_cost'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_cost', $object->getBaseCost());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_discount_amount', $object->getBaseDiscountAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_discount_refunded', $object->getBaseDiscountRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_hidden_tax_refunded', $object->getBaseHiddenTaxRefunded());
    }

    public function testGetBaseOriginalPrice()
    {
        $data = ['base_original_price' => 'test_value_base_original_price'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_original_price', $object->getBaseOriginalPrice());
    }

    public function testGetBasePrice()
    {
        $data = ['base_price' => 'test_value_base_price'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_price', $object->getBasePrice());
    }

    public function testGetBasePriceInclTax()
    {
        $data = ['base_price_incl_tax' => 'test_value_base_price_incl_tax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_price_incl_tax', $object->getBasePriceInclTax());
    }

    public function testGetBaseRowInvoiced()
    {
        $data = ['base_row_invoiced' => 'test_value_base_row_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_row_invoiced', $object->getBaseRowInvoiced());
    }

    public function testGetBaseRowTotal()
    {
        $data = ['base_row_total' => 'test_value_base_row_total'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_row_total', $object->getBaseRowTotal());
    }

    public function testGetBaseRowTotalInclTax()
    {
        $data = ['base_row_total_incl_tax' => 'test_value_base_row_total_incl_tax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_row_total_incl_tax', $object->getBaseRowTotalInclTax());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_amount', $object->getBaseTaxAmount());
    }

    public function testGetBaseTaxBeforeDiscount()
    {
        $data = ['base_tax_before_discount' => 'test_value_base_tax_before_discount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_before_discount', $object->getBaseTaxBeforeDiscount());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_refunded', $object->getBaseTaxRefunded());
    }

    public function testGetBaseWeeeTaxAppliedAmount()
    {
        $data = ['base_weee_tax_applied_amount' => 'test_value_base_weee_tax_applied_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_weee_tax_applied_amount', $object->getBaseWeeeTaxAppliedAmount());
    }

    public function testGetBaseWeeeTaxAppliedRowAmnt()
    {
        $data = ['base_weee_tax_applied_row_amnt' => 'test_value_base_weee_tax_applied_row_amnt'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_weee_tax_applied_row_amnt', $object->getBaseWeeeTaxAppliedRowAmnt());
    }

    public function testGetBaseWeeeTaxDisposition()
    {
        $data = ['base_weee_tax_disposition' => 'test_value_base_weee_tax_disposition'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_weee_tax_disposition', $object->getBaseWeeeTaxDisposition());
    }

    public function testGetBaseWeeeTaxRowDisposition()
    {
        $data = ['base_weee_tax_row_disposition' => 'test_value_base_weee_tax_row_disposition'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_weee_tax_row_disposition', $object->getBaseWeeeTaxRowDisposition());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_created_at', $object->getCreatedAt());
    }

    public function testGetDescription()
    {
        $data = ['description' => 'test_value_description'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_description', $object->getDescription());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_discount_amount', $object->getDiscountAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_discount_invoiced', $object->getDiscountInvoiced());
    }

    public function testGetDiscountPercent()
    {
        $data = ['discount_percent' => 'test_value_discount_percent'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_discount_percent', $object->getDiscountPercent());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_discount_refunded', $object->getDiscountRefunded());
    }

    public function testGetEventId()
    {
        $data = ['event_id' => 'test_value_event_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_event_id', $object->getEventId());
    }

    public function testGetExtOrderItemId()
    {
        $data = ['ext_order_item_id' => 'test_value_ext_order_item_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_ext_order_item_id', $object->getExtOrderItemId());
    }

    public function testGetFreeShipping()
    {
        $data = ['free_shipping' => 'test_value_free_shipping'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_free_shipping', $object->getFreeShipping());
    }

    public function testGetGwBasePrice()
    {
        $data = ['gw_base_price' => 'test_value_gw_base_price'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_base_price', $object->getGwBasePrice());
    }

    public function testGetGwBasePriceInvoiced()
    {
        $data = ['gw_base_price_invoiced' => 'test_value_gw_base_price_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_base_price_invoiced', $object->getGwBasePriceInvoiced());
    }

    public function testGetGwBasePriceRefunded()
    {
        $data = ['gw_base_price_refunded' => 'test_value_gw_base_price_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_base_price_refunded', $object->getGwBasePriceRefunded());
    }

    public function testGetGwBaseTaxAmount()
    {
        $data = ['gw_base_tax_amount' => 'test_value_gw_base_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_base_tax_amount', $object->getGwBaseTaxAmount());
    }

    public function testGetGwBaseTaxAmountInvoiced()
    {
        $data = ['gw_base_tax_amount_invoiced' => 'test_value_gw_base_tax_amount_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_base_tax_amount_invoiced', $object->getGwBaseTaxAmountInvoiced());
    }

    public function testGetGwBaseTaxAmountRefunded()
    {
        $data = ['gw_base_tax_amount_refunded' => 'test_value_gw_base_tax_amount_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_base_tax_amount_refunded', $object->getGwBaseTaxAmountRefunded());
    }

    public function testGetGwId()
    {
        $data = ['gw_id' => 'test_value_gw_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_id', $object->getGwId());
    }

    public function testGetGwPrice()
    {
        $data = ['gw_price' => 'test_value_gw_price'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_price', $object->getGwPrice());
    }

    public function testGetGwPriceInvoiced()
    {
        $data = ['gw_price_invoiced' => 'test_value_gw_price_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_price_invoiced', $object->getGwPriceInvoiced());
    }

    public function testGetGwPriceRefunded()
    {
        $data = ['gw_price_refunded' => 'test_value_gw_price_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_price_refunded', $object->getGwPriceRefunded());
    }

    public function testGetGwTaxAmount()
    {
        $data = ['gw_tax_amount' => 'test_value_gw_tax_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_tax_amount', $object->getGwTaxAmount());
    }

    public function testGetGwTaxAmountInvoiced()
    {
        $data = ['gw_tax_amount_invoiced' => 'test_value_gw_tax_amount_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_tax_amount_invoiced', $object->getGwTaxAmountInvoiced());
    }

    public function testGetGwTaxAmountRefunded()
    {
        $data = ['gw_tax_amount_refunded' => 'test_value_gw_tax_amount_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_gw_tax_amount_refunded', $object->getGwTaxAmountRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_hidden_tax_amount', $object->getHiddenTaxAmount());
    }

    public function testGetHiddenTaxCanceled()
    {
        $data = ['hidden_tax_canceled' => 'test_value_hidden_tax_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_hidden_tax_canceled', $object->getHiddenTaxCanceled());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_hidden_tax_refunded', $object->getHiddenTaxRefunded());
    }

    public function testGetIsNominal()
    {
        $data = ['is_nominal' => 'test_value_is_nominal'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_is_nominal', $object->getIsNominal());
    }

    public function testGetIsQtyDecimal()
    {
        $data = ['is_qty_decimal' => 'test_value_is_qty_decimal'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_is_qty_decimal', $object->getIsQtyDecimal());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_is_virtual', $object->getIsVirtual());
    }

    public function testGetItemId()
    {
        $data = ['item_id' => 'test_value_item_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_item_id', $object->getItemId());
    }

    public function testGetLockedDoInvoice()
    {
        $data = ['locked_do_invoice' => 'test_value_locked_do_invoice'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_locked_do_invoice', $object->getLockedDoInvoice());
    }

    public function testGetLockedDoShip()
    {
        $data = ['locked_do_ship' => 'test_value_locked_do_ship'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_locked_do_ship', $object->getLockedDoShip());
    }

    public function testGetName()
    {
        $data = ['name' => 'test_value_name'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_name', $object->getName());
    }

    public function testGetNoDiscount()
    {
        $data = ['no_discount' => 'test_value_no_discount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_no_discount', $object->getNoDiscount());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_order_id', $object->getOrderId());
    }

    public function testGetOriginalPrice()
    {
        $data = ['original_price' => 'test_value_original_price'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_original_price', $object->getOriginalPrice());
    }

    public function testGetParentItemId()
    {
        $data = ['parent_item_id' => 'test_value_parent_item_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_parent_item_id', $object->getParentItemId());
    }

    public function testGetPrice()
    {
        $data = ['price' => 'test_value_price'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_price', $object->getPrice());
    }

    public function testGetPriceInclTax()
    {
        $data = ['price_incl_tax' => 'test_value_price_incl_tax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_price_incl_tax', $object->getPriceInclTax());
    }

    public function testGetProductId()
    {
        $data = ['product_id' => 'test_value_product_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_product_id', $object->getProductId());
    }

    public function testGetProductOptions()
    {
        $data = ['product_options' => 'test_value_product_options'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_product_options', $object->getProductOptions());
    }

    public function testGetProductType()
    {
        $data = ['product_type' => 'test_value_product_type'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_product_type', $object->getProductType());
    }

    public function testGetQtyBackordered()
    {
        $data = ['qty_backordered' => 'test_value_qty_backordered'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_qty_backordered', $object->getQtyBackordered());
    }

    public function testGetQtyCanceled()
    {
        $data = ['qty_canceled' => 'test_value_qty_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_qty_canceled', $object->getQtyCanceled());
    }

    public function testGetQtyInvoiced()
    {
        $data = ['qty_invoiced' => 'test_value_qty_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_qty_invoiced', $object->getQtyInvoiced());
    }

    public function testGetQtyOrdered()
    {
        $data = ['qty_ordered' => 'test_value_qty_ordered'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_qty_ordered', $object->getQtyOrdered());
    }

    public function testGetQtyRefunded()
    {
        $data = ['qty_refunded' => 'test_value_qty_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_qty_refunded', $object->getQtyRefunded());
    }

    public function testGetQtyReturned()
    {
        $data = ['qty_returned' => 'test_value_qty_returned'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_qty_returned', $object->getQtyReturned());
    }

    public function testGetQtyShipped()
    {
        $data = ['qty_shipped' => 'test_value_qty_shipped'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_qty_shipped', $object->getQtyShipped());
    }

    public function testGetQuoteItemId()
    {
        $data = ['quote_item_id' => 'test_value_quote_item_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_quote_item_id', $object->getQuoteItemId());
    }

    public function testGetRowInvoiced()
    {
        $data = ['row_invoiced' => 'test_value_row_invoiced'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_row_invoiced', $object->getRowInvoiced());
    }

    public function testGetRowTotal()
    {
        $data = ['row_total' => 'test_value_row_total'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_row_total', $object->getRowTotal());
    }

    public function testGetRowTotalInclTax()
    {
        $data = ['row_total_incl_tax' => 'test_value_row_total_incl_tax'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_row_total_incl_tax', $object->getRowTotalInclTax());
    }

    public function testGetRowWeight()
    {
        $data = ['row_weight' => 'test_value_row_weight'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_row_weight', $object->getRowWeight());
    }

    public function testGetSku()
    {
        $data = ['sku' => 'test_value_sku'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_sku', $object->getSku());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_store_id', $object->getStoreId());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_tax_amount', $object->getTaxAmount());
    }

    public function testGetTaxBeforeDiscount()
    {
        $data = ['tax_before_discount' => 'test_value_tax_before_discount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_tax_before_discount', $object->getTaxBeforeDiscount());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_tax_invoiced', $object->getTaxInvoiced());
    }

    public function testGetTaxPercent()
    {
        $data = ['tax_percent' => 'test_value_tax_percent'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_tax_percent', $object->getTaxPercent());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_tax_refunded', $object->getTaxRefunded());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_updated_at', $object->getUpdatedAt());
    }

    public function testGetWeeeTaxApplied()
    {
        $data = ['weee_tax_applied' => 'test_value_weee_tax_applied'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_weee_tax_applied', $object->getWeeeTaxApplied());
    }

    public function testGetWeeeTaxAppliedAmount()
    {
        $data = ['weee_tax_applied_amount' => 'test_value_weee_tax_applied_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_weee_tax_applied_amount', $object->getWeeeTaxAppliedAmount());
    }

    public function testGetWeeeTaxAppliedRowAmount()
    {
        $data = ['weee_tax_applied_row_amount' => 'test_value_weee_tax_applied_row_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_weee_tax_applied_row_amount', $object->getWeeeTaxAppliedRowAmount());
    }

    public function testGetWeeeTaxDisposition()
    {
        $data = ['weee_tax_disposition' => 'test_value_weee_tax_disposition'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_weee_tax_disposition', $object->getWeeeTaxDisposition());
    }

    public function testGetWeeeTaxRowDisposition()
    {
        $data = ['weee_tax_row_disposition' => 'test_value_weee_tax_row_disposition'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_weee_tax_row_disposition', $object->getWeeeTaxRowDisposition());
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

        $object = new \Magento\Sales\Service\V1\Data\OrderItem($abstractBuilderMock);

        $this->assertEquals('test_value_weight', $object->getWeight());
    }
}
