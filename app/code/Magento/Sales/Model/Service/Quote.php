<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

/**
 * Class Quote
 * Quote submit service model
 */
class Quote
{
    /**
     * Quote object
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote;

    /**
     * Quote convert object
     *
     * @var \Magento\Sales\Model\Convert\Quote
     */
    protected $_convertor;

    /**
     * List of additional order attributes which will be added to order before save
     *
     * @var array
     */
    protected $_orderData = [];

    /**
     * Order that may be created during submission
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order = null;

    /**
     * If it is true, quote will be inactivate after submitting order
     *
     * @var bool
     */
    protected $_shouldInactivateQuote = true;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * Account management
     *
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * Customer builder
     *
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    protected $customerBuilder;

    /**
     * Address builder
     *
     * @var \Magento\Customer\Api\Data\AddressDataBuilder
     */
    protected $addressBuilder;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\RegionDataBuilder
     */
    protected $regionBuilder;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Quote $quote
     * @param \Magento\Sales\Model\Convert\QuoteFactory $convertQuoteFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder
     * @param \Magento\Customer\Api\Data\AddressDataBuilder $addressBuilder
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\Data\RegionDataBuilder $regionDataBuilder
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Quote $quote,
        \Magento\Sales\Model\Convert\QuoteFactory $convertQuoteFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder,
        \Magento\Customer\Api\Data\AddressDataBuilder $addressBuilder,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\Data\RegionDataBuilder $regionDataBuilder
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerBuilder = $customerBuilder;
        $this->addressBuilder = $addressBuilder;
        $this->_eventManager = $eventManager;
        $this->_quote = $quote;
        $this->_convertor = $convertQuoteFactory->create();
        $this->_customerSession = $customerSession;
        $this->_transactionFactory = $transactionFactory;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->regionBuilder = $regionDataBuilder;
    }

    /**
     * Quote convertor declaration
     *
     * @param \Magento\Sales\Model\Convert\Quote $convertor
     * @return $this
     */
    public function setConvertor(\Magento\Sales\Model\Convert\Quote $convertor)
    {
        $this->_convertor = $convertor;
        return $this;
    }

    /**
     * Get assigned quote object
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Specify additional order data
     *
     * @param array $data
     * @return $this
     */
    public function setOrderData(array $data)
    {
        $this->_orderData = $data;
        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Quote $quote
     * @return void
     */
    protected function prepareCustomerData(\Magento\Sales\Model\Quote $quote)
    {
        $customer = $quote->getCustomer();

        if (!$customer->getId()) {
            $customer = $this->accountManagement->createAccountWithPasswordHash(
                $this->customerBuilder->populate($customer)->create(),
                $quote->getPasswordHash()
            );
        } else {
            $this->customerRepository->save($customer);
        }

        if (!$quote->getBillingAddress()->getId() && $customer->getDefaultBilling()) {
            $quote->getBillingAddress()->importCustomerAddressData(
                $this->addressRepository->getById($customer->getDefaultBilling())
            );
        }
        if (!$quote->getShippingAddress()->getSameAsBilling()
            && !$quote->getBillingAddress()->getId()
            && $customer->getDefaultShipping()
        ) {
            $quote->getShippingAddress()->importCustomerAddressData(
                $this->addressRepository->getById($customer->getDefaultShipping())
            );
        }
        $quote->setCustomer($customer);
    }

    /**
     * Submit the quote. Quote submit process will create the order based on quote data
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function submitOrderWithDataObject()
    {
        $this->_validate();
        $quote = $this->_quote;
        $isVirtual = $quote->isVirtual();

        $transaction = $this->_transactionFactory->create();
        if (!$quote->getCustomerIsGuest()) {
            $this->prepareCustomerData($quote);
        }
        $transaction->addObject($quote);

        $quote->reserveOrderId();
        if ($isVirtual) {
            $order = $this->_convertor->addressToOrder($quote->getBillingAddress());
        } else {
            $order = $this->_convertor->addressToOrder($quote->getShippingAddress());
        }

        $order->setBillingAddress($this->_convertor->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddress()) {
            $order->getBillingAddress()->setCustomerAddressData($quote->getBillingAddress()->getCustomerAddress());
        }

        if (!$isVirtual) {
            $order->setShippingAddress($this->_convertor->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $order->getShippingAddress()->setCustomerAddressData(
                    $quote->getShippingAddress()->getCustomerAddress()
                );
            }
        }
        $order->setPayment($this->_convertor->paymentToOrderPayment($quote->getPayment()));

        foreach ($this->_orderData as $key => $value) {
            $order->setData($key, $value);
        }

        foreach ($quote->getAllItems() as $item) {
            $orderItem = $this->_convertor->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        $customer = $quote->getCustomer();
        if ($customer) {
            $order->setCustomerId($customer->getId());
        }
        $order->setQuote($quote);

        $transaction->addObject($order);

        /**
         * We can use configuration data for declare new order status
         */
        $this->_eventManager->dispatch(
            'sales_model_service_quote_submit_before',
            ['order' => $order, 'quote' => $quote]
        );
        try {
            $order->place();
            $transaction->save();
            $this->_inactivateQuote();
            $this->_eventManager->dispatch(
                'sales_model_service_quote_submit_success',
                ['order' => $order, 'quote' => $quote]
            );
        } catch (\Exception $e) {
            //reset order ID's on exception, because order not saved
            $order->setId(null);

            /** @var $item \Magento\Sales\Model\Order\Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            $this->_eventManager->dispatch(
                'sales_model_service_quote_submit_failure',
                ['order' => $order, 'quote' => $quote]
            );
            throw $e;
        }
        $this->_order = $order;

        return $order;
    }

    /**
     * Submit all available items
     * All created items will be set to the object
     *
     * @return void
     * @throws \Exception
     */
    public function submitAllWithDataObject()
    {
        // no need to submit the order if there are no normal items remained
        if (!$this->_quote->getAllVisibleItems()) {
            $this->_inactivateQuote();
            return;
        }
        $this->submitOrderWithDataObject();
    }

    /**
     * Get an order that may had been created during submission
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Inactivate quote
     *
     * @return $this
     */
    protected function _inactivateQuote()
    {
        if ($this->_shouldInactivateQuote) {
            $this->_quote->setIsActive(false);
        }
        return $this;
    }

    /**
     * Validate quote data before converting to order
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _validate()
    {
        if (!$this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                throw new \Magento\Framework\Model\Exception(
                    __('Please check the shipping address information. %1', implode(' ', $addressValidation))
                );
            }
            $method = $address->getShippingMethod();
            $rate = $address->getShippingRateByCode($method);
            if (!$this->getQuote()->isVirtual() && (!$method || !$rate)) {
                throw new \Magento\Framework\Model\Exception(__('Please specify a shipping method.'));
            }
        }

        $addressValidation = $this->getQuote()->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            throw new \Magento\Framework\Model\Exception(
                __('Please check the billing address information. %1', implode(' ', $addressValidation))
            );
        }

        if (!$this->getQuote()->getPayment()->getMethod()) {
            throw new \Magento\Framework\Model\Exception(__('Please select a valid payment method.'));
        }

        return $this;
    }
}
