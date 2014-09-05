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

class InvoiceItemTest extends \PHPUnit_Framework_TestCase
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_additional_data', $object->getAdditionalData());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_discount_amount', $object->getBaseDiscountAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_hidden_tax_amount', $object->getBaseHiddenTaxAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_price_incl_tax', $object->getBasePriceInclTax());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_base_tax_amount', $object->getBaseTaxAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_discount_amount', $object->getDiscountAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_entity_id', $object->getEntityId());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_hidden_tax_amount', $object->getHiddenTaxAmount());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_name', $object->getName());
    }

    public function testGetOrderItemId()
    {
        $data = ['order_item_id' => 'test_value_order_item_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_order_item_id', $object->getOrderItemId());
    }

    public function testGetParentId()
    {
        $data = ['parent_id' => 'test_value_parent_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_parent_id', $object->getParentId());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_product_id', $object->getProductId());
    }

    public function testGetQty()
    {
        $data = ['qty' => 'test_value_qty'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_qty', $object->getQty());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_row_total_incl_tax', $object->getRowTotalInclTax());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_sku', $object->getSku());
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

        $object = new \Magento\Sales\Service\V1\Data\InvoiceItem($abstractBuilderMock);

        $this->assertEquals('test_value_tax_amount', $object->getTaxAmount());
    }
}
