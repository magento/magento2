<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Quote\Model;

use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Api\Data\OrderDataBuilder as OrderBuilder;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;
use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;

/**
 * Class QuoteManagement
 */
class QuoteManagement
{
    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var QuoteValidator
     */
    protected $quoteValidator;

    /**
     * @var OrderBuilder|\Magento\Framework\Api\Builder
     */
    protected $orderBuilder;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var ToOrderConverter
     */
    protected $quoteAddressToOrder;

    /**
     * @var ToOrderAddressConverter
     */
    protected $quoteAddressToOrderAddress;

    /**
     * @var ToOrderItemConverter
     */
    protected $quoteItemToOrderItem;

    /**
     * @var ToOrderPaymentConverter
     */
    protected $quotePaymentToOrderPayment;

    /**
     * @param EventManager $eventManager
     * @param QuoteValidator $quoteValidator
     * @param OrderBuilder $orderBuilder
     * @param OrderManagement $orderManagement
     * @param CustomerManagement $customerManagement
     * @param ToOrderConverter $quoteAddressToOrder
     * @param ToOrderAddressConverter $quoteAddressToOrderAddress
     * @param ToOrderItemConverter $quoteItemToOrderItem
     * @param ToOrderPaymentConverter $quotePaymentToOrderPayment
     */
    public function __construct(
        EventManager $eventManager,
        QuoteValidator $quoteValidator,
        OrderBuilder $orderBuilder,
        OrderManagement $orderManagement,
        CustomerManagement $customerManagement,
        ToOrderConverter $quoteAddressToOrder,
        ToOrderAddressConverter $quoteAddressToOrderAddress,
        ToOrderItemConverter $quoteItemToOrderItem,
        ToOrderPaymentConverter $quotePaymentToOrderPayment
    ) {
        $this->eventManager = $eventManager;
        $this->quoteValidator = $quoteValidator;
        $this->orderBuilder = $orderBuilder;
        $this->orderManagement = $orderManagement;
        $this->customerManagement = $customerManagement;
        $this->quoteAddressToOrder = $quoteAddressToOrder;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
    }

    /**
     * @param Quote $quote
     */
    protected function deleteNominalItems(QuoteEntity $quote)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->isNominal()) {
                $item->isDeleted(true);
            }
        }
    }

    /**
     * @param Quote $quote
     * @throws \Magento\Framework\Model\Exception
     */
    public function submitNominalItems(QuoteEntity $quote)
    {
        $this->quoteValidator->validateBeforeSubmit($quote);
        $this->eventManager->dispatch(
            'sales_model_service_quote_submit_nominal_items',
            [
                'quote' => $quote
            ]
        );
        $quote->setIsActive(false);
        $this->deleteNominalItems($quote);
    }

    /**
     * @param Quote $quote
     * @param array $orderData
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object|void
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    public function submit(QuoteEntity $quote, $orderData = [])
    {
        if (!$quote->getAllVisibleItems()) {
            $quote->setIsActive(false);
            return;
        }
        $this->eventManager->dispatch(
            'sales_model_service_quote_submit_nominal_items',
            [
                'quote' => $quote
            ]
        );
        $this->deleteNominalItems($quote);
        return $this->submitQuote($quote, $orderData);
    }

    /**
     * @param Quote $quote
     * @return array
     */
    protected function resolveItems(QuoteEntity $quote)
    {
        $quoteItems = $quote->getAllItems();
        for ($i = 0; $i < count($quoteItems) - 1; $i++) {
            for ($j = 0; $j < count($quoteItems) - $i - 1; $j++) {
                if ($quoteItems[$i]->getParentItemId() == $quoteItems[$j]->getId()) {
                    $tempItem = $quoteItems[$i];
                    $quoteItems[$i] = $quoteItems[$j];
                    $quoteItems[$j] = $tempItem;
                }
            }
        }
        $orderItems = [];
        foreach ($quoteItems as $quoteItem) {
            $parentItem = (isset($orderItems[$quoteItem->getParentItemId()])) ?
                $orderItems[$quoteItem->getParentItemId()] : null;
            $orderItems[$quoteItem->getId()] =
                $this->quoteItemToOrderItem->convert($quoteItem, ['parent_item' => $parentItem]);
        }
        return array_values($orderItems);
    }

    /**
     * Submit quote
     *
     * @param Quote $quote
     * @param array $orderData
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    protected function submitQuote(QuoteEntity $quote, $orderData = [])
    {
        $this->quoteValidator->validateBeforeSubmit($quote);
        if (!$quote->getCustomerIsGuest()) {
            $this->customerManagement->populateCustomerInfo($quote);
        }
        $addresses = [];
        if ($quote->isVirtual()) {
            $this->orderBuilder->populate(
                $this->quoteAddressToOrder->convert($quote->getBillingAddress(), $orderData)
            );
        } else {
            $this->orderBuilder->populate(
                $this->quoteAddressToOrder->convert($quote->getShippingAddress(), $orderData)
            );
            $shippingAddress = $this->quoteAddressToOrderAddress->convert(
                $quote->getShippingAddress(), ['address_type' => 'shipping']
            );
            $addresses[] = $shippingAddress;
            $this->orderBuilder->setShippingAddress($shippingAddress);

        }
        $billingAddress = $this->quoteAddressToOrderAddress->convert(
            $quote->getBillingAddress(), ['address_type' => 'billing']
        );
        $addresses[] = $billingAddress;
        $this->orderBuilder->setBillingAddress($billingAddress);
        $this->orderBuilder->setAddresses($addresses);
        $this->orderBuilder->setPayments(
            [$this->quotePaymentToOrderPayment->convert($quote->getPayment())]
        );
        $this->orderBuilder->setItems($this->resolveItems($quote));
        if ($quote->getCustomer()) {
            $this->orderBuilder->setCustomerId($quote->getCustomer()->getId());
        }
        $this->orderBuilder->setQuoteId($quote->getId());
        $order = $this->orderBuilder->create();
        $this->eventManager->dispatch(
            'sales_model_service_quote_submit_before',
            [
                'order' => $order,
                'quote' => $quote
            ]
        );
        try {
            $order = $this->orderManagement->place($order);
            $quote->setIsActive(false);
            $this->eventManager->dispatch(
                'sales_model_service_quote_submit_success',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );
        } catch (\Exception $e) {
            $this->eventManager->dispatch(
                'sales_model_service_quote_submit_failure',
                [
                    'order' => $order,
                    'quote' => $quote
                ]
            );
            throw $e;
        }
        return $order;
    }
}
