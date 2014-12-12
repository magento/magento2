<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tax\Helper;

use Magento\Sales\Model\Quote\Address;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $helper;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $orderTaxManagementMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $priceCurrencyMock;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->orderTaxManagementMock = $this->getMockBuilder('Magento\Tax\Api\OrderTaxManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrencyMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $objectManager->getObject(
            'Magento\Tax\Helper\Data',
            [
                'orderTaxManagement' => $this->orderTaxManagementMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    public function testGetCalculatedTaxesEmptySource()
    {
        $source = null;
        $this->assertEquals([], $this->helper->getCalculatedTaxes($source));
    }

    public function testGetCalculatedTaxesForOrder()
    {
        $orderId = 1;
        $itemCode = 'test_code';
        $itemAmount = 2;
        $itemBaseAmount = 3;
        $itemTitle = 'Test title';
        $itemPercent = 0.1;

        $expectedAmount = $itemAmount + 1;
        $expectedBaseAmount = $itemBaseAmount + 1;

        $orderDetailsItem = $this->getMockBuilder('Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $orderDetailsItem->expects($this->once())
            ->method('getCode')
            ->willReturn($itemCode);
        $orderDetailsItem->expects($this->once())
            ->method('getAmount')
            ->willReturn($itemAmount);
        $orderDetailsItem->expects($this->once())
            ->method('getBaseAmount')
            ->willReturn($itemBaseAmount);
        $orderDetailsItem->expects($this->once())
            ->method('getTitle')
            ->willReturn($itemTitle);
        $orderDetailsItem->expects($this->once())
            ->method('getPercent')
            ->willReturn($itemPercent);

        $roundValues = [
            [$itemAmount, $expectedAmount],
            [$itemBaseAmount, $expectedBaseAmount],
        ];
        $this->priceCurrencyMock->expects($this->exactly(2))
            ->method('round')
            ->will($this->returnValueMap($roundValues));

        $appliedTaxes = [$orderDetailsItem];

        $orderDetails = $this->getMockBuilder('Magento\Tax\Api\Data\OrderTaxDetailsInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $orderDetails->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $this->orderTaxManagementMock->expects($this->once())
            ->method('getOrderTaxDetails')
            ->with($orderId)
            ->willReturn($orderDetails);

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);

        $result = $this->helper->getCalculatedTaxes($orderMock);
        $this->assertCount(1, $result);
        $this->assertEquals($expectedAmount, $result[0]['tax_amount']);
        $this->assertEquals($expectedBaseAmount, $result[0]['base_tax_amount']);
        $this->assertEquals($itemTitle, $result[0]['title']);
        $this->assertEquals($itemPercent, $result[0]['percent']);
    }

    public function testGetCalculatedTaxesForOrderItems()
    {
        $orderId = 1;
        $itemShippingTaxAmount = 1;
        $orderShippingTaxAmount = 1;
        $itemCode = 'test_code';
        $itemAmount = 1;
        $itemBaseAmount = 2;
        $itemTitle = 'Test title';
        $itemPercent = 0.1;
        $failedTaxAmount = "0.00000";

        $expectedAmount = 2;
        $expectedBaseAmount = 4;

        $orderDetailsItemNormal = $this->getMockBuilder('Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $orderDetailsItemNormal->expects($this->once())
            ->method('getCode')
            ->willReturn($itemCode);
        $orderDetailsItemNormal->expects($this->once())
            ->method('getAmount')
            ->willReturn($itemAmount);
        $orderDetailsItemNormal->expects($this->once())
            ->method('getBaseAmount')
            ->willReturn($itemBaseAmount);
        $orderDetailsItemNormal->expects($this->once())
            ->method('getTitle')
            ->willReturn($itemTitle);
        $orderDetailsItemNormal->expects($this->once())
            ->method('getPercent')
            ->willReturn($itemPercent);

        $orderDetailsItemZeroAmount = $this->getMockBuilder('Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $orderDetailsItemZeroAmount->expects($this->once())
            ->method('getAmount')
            ->willReturn(0);
        $orderDetailsItemZeroAmount->expects($this->once())
            ->method('getBaseAmount')
            ->willReturn(0);

        $appliedTaxes = [$orderDetailsItemNormal, $orderDetailsItemZeroAmount];

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('getShippingTaxAmount')
            ->willReturn($orderShippingTaxAmount);

        $taxDetailsData = $this->getMockBuilder('Magento\Tax\Api\Data\OrderTaxDetailsItemInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $taxDetailsData->expects($this->once())
            ->method('getType')
            ->willReturn(Address::TYPE_SHIPPING);
        $taxDetailsData->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);

        $orderDetails = $this->getMockBuilder('Magento\Tax\Api\Data\OrderTaxDetailsInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $orderDetails->expects($this->once())
            ->method('getItems')
            ->willReturn([$taxDetailsData]);

        $this->orderTaxManagementMock->expects($this->once())
            ->method('getOrderTaxDetails')
            ->with($orderId)
            ->willReturn($orderDetails);

        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getTaxAmount')
            ->willReturn($failedTaxAmount);

        $invoiceItemFailed = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceItemFailed->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($orderItemMock);
        $invoiceItemFailed->expects($this->once())
            ->method('getTaxAmount')
            ->willReturn(1);

        $source = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->getMock();
        $source->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $source->expects($this->once())
            ->method('getShippingTaxAmount')
            ->willReturn($itemShippingTaxAmount);
        $source->expects($this->once())
            ->method('getItemsCollection')
            ->willReturn([$invoiceItemFailed]);

        $roundValues = [
            [$itemAmount, $expectedAmount],
            [$itemBaseAmount, $expectedBaseAmount],
        ];
        $this->priceCurrencyMock->expects($this->exactly(2))
            ->method('round')
            ->will($this->returnValueMap($roundValues));

        $result = $this->helper->getCalculatedTaxes($source);
        $this->assertCount(1, $result);
        $this->assertEquals($expectedAmount, $result[0]['tax_amount']);
        $this->assertEquals($expectedBaseAmount, $result[0]['base_tax_amount']);
        $this->assertEquals($itemTitle, $result[0]['title']);
        $this->assertEquals($itemPercent, $result[0]['percent']);
    }
}
