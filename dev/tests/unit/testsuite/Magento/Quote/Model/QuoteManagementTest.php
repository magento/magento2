<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model;

class QuoteManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuoteManagement
     */
    protected $model;

    /**
     * @var \Magento\Quote\Model\QuoteValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteValidator;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Sales\Api\Data\OrderDataBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderBuilder;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressToOrder;

    /**
     * @var \Magento\Quote\Model\Quote\Payment\ToOrderPayment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quotePaymentToOrderPayment;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrderAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressToOrderAddress;

    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemToOrderItem;

    /**
     * @var \Magento\Quote\Model\Quote\Payment\ToOrderPayment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderManagement;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->quoteValidator = $this->getMock('Magento\Quote\Model\QuoteValidator', [], [], '', false);
        $this->eventManager = $this->getMockForAbstractClass('Magento\Framework\Event\ManagerInterface');
        $this->orderBuilder = $this->getMock(
            'Magento\Sales\Api\Data\OrderDataBuilder',
            [
                'populate', 'setShippingAddress', 'setBillingAddress', 'setAddresses', 'setPayments',
                'setItems', 'setCustomerId', 'setQuoteId', 'create', 'setCustomerEmail', 'setCustomerFirstname',
                'setCustomerMiddlename', 'setCustomerLastname'
            ],
            [],
            '',
            false
        );
        $this->quoteAddressToOrder = $this->getMock(
            'Magento\Quote\Model\Quote\Address\ToOrder',
            [],
            [],
            '',
            false
        );
        $this->quotePaymentToOrderPayment = $this->getMock(
            'Magento\Quote\Model\Quote\Payment\ToOrderPayment',
            [],
            [],
            '',
            false
        );
        $this->quoteAddressToOrderAddress = $this->getMock(
            'Magento\Quote\Model\Quote\Address\ToOrderAddress',
            [],
            [],
            '',
            false
        );
        $this->quoteItemToOrderItem = $this->getMock('Magento\Quote\Model\Quote\Item\ToOrderItem', [], [], '', false);
        $this->orderManagement = $this->getMock('Magento\Sales\Api\OrderManagementInterface', [], [], '', false);
        $this->customerManagement = $this->getMock('Magento\Quote\Model\CustomerManagement', [], [], '', false);

        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\QuoteManagement',
            [
                'quoteValidator' => $this->quoteValidator,
                'eventManager' => $this->eventManager,
                'orderBuilder' => $this->orderBuilder,
                'orderManagement' => $this->orderManagement,
                'customerManagement' => $this->customerManagement,
                'quoteAddressToOrder' => $this->quoteAddressToOrder,
                'quoteAddressToOrderAddress' => $this->quoteAddressToOrderAddress,
                'quoteItemToOrderItem' => $this->quoteItemToOrderItem,
                'quotePaymentToOrderPayment' => $this->quotePaymentToOrderPayment
            ]
        );
    }

    public function testSubmitNominalItems()
    {
        $quoteItem = $this->getQuoteItem(false);
        $nominalQuoteItem = $this->getQuoteItem(true);
        $nominalQuoteItem->expects($this->once())
            ->method('isDeleted')
            ->with(true);

        $quote = $this->getMock('Magento\Quote\Model\Quote', ['setIsActive', 'getAllVisibleItems'], [], '', false);
        $quote->expects($this->once())
            ->method('setIsActive')
            ->with(false);
        $quote->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([$quoteItem, $nominalQuoteItem]);

        $this->quoteValidator->expects($this->once())
            ->method('validateBeforeSubmit')
            ->with($quote);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('sales_model_service_quote_submit_nominal_items', ['quote' => $quote]);

        $this->model->submitNominalItems($quote);
    }

    public function testSubmit()
    {
        $orderData = [];
        $isGuest = true;
        $isVirtual = false;
        $customerId = 1;
        $quoteId = 1;
        $quoteItem = $this->getQuoteItem(false);

        $billingAddress = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $shippingAddress = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $payment = $this->getMock('Magento\Quote\Model\Quote\Payment', [], [], '', false);

        $baseOrder = $this->getMock('Magento\Sales\Api\Data\OrderInterface', [], [], '', false);
        $convertedBillingAddress = $this->getMock('Magento\Sales\Api\Data\OrderAddressInterface', [], [], '', false);
        $convertedShippingAddress = $this->getMock('Magento\Sales\Api\Data\OrderAddressInterface', [], [], '', false);
        $convertedPayment = $this->getMock('Magento\Sales\Api\Data\OrderPaymentInterface', [], [], '', false);
        $convertedQuoteItem = $this->getMock('Magento\Sales\Api\Data\OrderItemInterface', [], [], '', false);

        $addresses = [$convertedShippingAddress, $convertedBillingAddress];
        $payments = [$convertedPayment];
        $quoteItems = [$quoteItem];
        $convertedItems = [$convertedQuoteItem];

        $quote = $this->getQuote(
            $isGuest,
            $isVirtual,
            $billingAddress,
            $payment,
            $customerId,
            $quoteId,
            $quoteItems,
            $shippingAddress
        );

        $this->quoteValidator->expects($this->once())
            ->method('validateBeforeSubmit')
            ->with($quote);

        $this->quoteAddressToOrder->expects($this->once())
            ->method('convert')
            ->with($shippingAddress, $orderData)
            ->willReturn($baseOrder);
        $this->quoteAddressToOrderAddress->expects($this->at(0))
            ->method('convert')
            ->with($shippingAddress,
                [
                    'address_type' => 'shipping',
                    'email' => 'customer@example.com'
                ]
            )
            ->willReturn($convertedShippingAddress);
        $this->quoteAddressToOrderAddress->expects($this->at(1))
            ->method('convert')
            ->with($billingAddress,
                [
                    'address_type' => 'billing',
                    'email' => 'customer@example.com'
                ]
            )
            ->willReturn($convertedBillingAddress);
        $this->quoteItemToOrderItem->expects($this->once())
            ->method('convert')
            ->with($quoteItem, ['parent_item' => null])
            ->willReturn($convertedQuoteItem);
        $this->quotePaymentToOrderPayment->expects($this->once())
            ->method('convert')
            ->with($payment)
            ->willReturn($convertedPayment);

        $order = $this->prepareOrderBuilder(
            $baseOrder,
            $convertedBillingAddress,
            $addresses,
            $payments,
            $convertedItems,
            $quoteId,
            $convertedShippingAddress
        );

        $this->orderManagement->expects($this->once())
            ->method('place')
            ->with($order)
            ->willReturn($order);

        $this->eventManager->expects($this->at(0))
            ->method('dispatch')
            ->with('sales_model_service_quote_submit_nominal_items', ['quote' => $quote]);
        $this->eventManager->expects($this->at(1))
            ->method('dispatch')
            ->with('sales_model_service_quote_submit_before', ['order' => $order, 'quote' => $quote]);
        $this->eventManager->expects($this->at(2))
            ->method('dispatch')
            ->with('sales_model_service_quote_submit_success', ['order' => $order, 'quote' => $quote]);

        $this->assertEquals($order, $this->model->submit($quote, $orderData));

    }

    protected function getQuote(
        $isGuest,
        $isVirtual,
        \Magento\Quote\Model\Quote\Address $billingAddress,
        \Magento\Quote\Model\Quote\Payment $payment,
        $customerId,
        $id,
        array $quoteItems,
        \Magento\Quote\Model\Quote\Address $shippingAddress = null
    ) {
        $quote = $this->getMock(
            'Magento\Quote\Model\Quote',
            [
                'setIsActive',
                'getCustomerEmail',
                'getAllVisibleItems',
                'getCustomerIsGuest',
                'isVirtual',
                'getBillingAddress',
                'getShippingAddress',
                'getId',
                'getCustomer',
                'getAllItems',
                'getPayment'
            ],
            [],
            '',
            false
        );
        $quote->expects($this->once())
            ->method('setIsActive')
            ->with(false);
        $quote->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn($quoteItems);
        $quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn($quoteItems);
        $quote->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn($isGuest);
        $quote->expects($this->once())
            ->method('isVirtual')
            ->willReturn($isVirtual);
        if ($shippingAddress) {
            $quote->expects($this->exactly(2))
                ->method('getShippingAddress')
                ->willReturn($shippingAddress);
        }
        $quote->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $quote->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $customer = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $quote->expects($this->atLeastOnce())
            ->method('getCustomerEmail')
            ->willReturn('customer@example.com');
        $quote->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $quote->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        return $quote;
    }

    protected function getQuoteItem($isNominal)
    {
        $quoteItem = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $quoteItem->expects($this->once())
            ->method('isNominal')
            ->willReturn($isNominal);
        return $quoteItem;
    }

    protected function prepareOrderBuilder(
        \Magento\Sales\Api\Data\OrderInterface $baseOrder,
        \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress,
        array $addresses,
        array $payments,
        array $items,
        $quoteId,
        \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress = null,
        $customerId = null
    ) {
        $this->orderBuilder->expects($this->once())
            ->method('populate')
            ->with($baseOrder);
        if ($shippingAddress) {
            $this->orderBuilder->expects($this->once())
                ->method('setShippingAddress')
                ->with($shippingAddress);
        }
        $this->orderBuilder->expects($this->once())
            ->method('setBillingAddress')
            ->with($billingAddress);
        $this->orderBuilder->expects($this->once())
            ->method('setAddresses')
            ->with($addresses);
        $this->orderBuilder->expects($this->once())
            ->method('setPayments')
            ->with($payments);
        $this->orderBuilder->expects($this->once())
            ->method('setItems')
            ->with($items);
        if ($customerId) {
            $this->orderBuilder->expects($this->once())
                ->method('setCustomerId')
                ->with($customerId);
        }
        $this->orderBuilder->expects($this->once())
            ->method('setQuoteId')
            ->with($quoteId);

        $order = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);

        $this->orderBuilder->expects($this->once())
            ->method('create')
            ->willReturn($order);

        return $order;
    }
}
