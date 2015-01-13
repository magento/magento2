<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Checkout\Type;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Multishipping checkout model
 */
class Multishipping extends \Magento\Framework\Object
{
    /**
     * Quote shipping addresses items cache
     *
     * @var array
     */
    protected $_quoteShippingAddressesItems;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Quote\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Sales\Model\Convert\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Payment\Model\Method\SpecificationInterface
     */
    protected $paymentSpecification;

    /**
     * Initialize dependencies.
     *
     * @var \Magento\Multishipping\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Sales\Model\Quote\AddressFactory $addressFactory
     * @param \Magento\Sales\Model\Convert\Quote $quote
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification
     * @param \Magento\Multishipping\Helper\Data $helper
     * @param OrderSender $orderSender
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder,
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\Generic $session,
        \Magento\Sales\Model\Quote\AddressFactory $addressFactory,
        \Magento\Sales\Model\Convert\Quote $quote,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification,
        \Magento\Multishipping\Helper\Data $helper,
        OrderSender $orderSender,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_session = $session;
        $this->_addressFactory = $addressFactory;
        $this->_quote = $quote;
        $this->_storeManager = $storeManager;
        $this->paymentSpecification = $paymentSpecification;
        $this->helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->addressRepository = $addressRepository;
        $this->orderSender = $orderSender;
        $this->priceCurrency = $priceCurrency;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($data);
        $this->_init();
    }

    /**
     * Initialize multishipping checkout.
     * Split virtual/not virtual items between default billing/shipping addresses
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected function _init()
    {
        /**
         * reset quote shipping addresses and items
         */
        $quote = $this->getQuote();
        if (!$this->getCustomer()->getId()) {
            return $this;
        }

        if ($this->getCheckoutSession()->getCheckoutState() === \Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN
        ) {
            $this->getCheckoutSession()->setCheckoutState(true);
            /**
             * Remove all addresses
             */
            $addresses = $quote->getAllAddresses();
            foreach ($addresses as $address) {
                $quote->removeAddress($address->getId());
            }

            $defaultShippingId = $this->getCustomerDefaultShippingAddress();
            if ($defaultShippingId) {
                $quote->getShippingAddress()->importCustomerAddressData(
                    $this->addressRepository->getById($defaultShippingId)
                );

                foreach ($this->getQuoteItems() as $item) {
                    /**
                     * Items with parent id we add in importQuoteItem method.
                     * Skip virtual items
                     */
                    if ($item->getParentItemId() || $item->getProduct()->getIsVirtual()) {
                        continue;
                    }
                    $quote->getShippingAddress()->addItem($item);
                }
            }

            $defaultBillingAddressId = $this->getCustomerDefaultBillingAddress();
            if ($defaultBillingAddressId) {
                $quote->getBillingAddress()->importCustomerAddressData(
                    $this->addressRepository->getById($defaultBillingAddressId)
                );
                foreach ($this->getQuoteItems() as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($item->getProduct()->getIsVirtual()) {
                        $quote->getBillingAddress()->addItem($item);
                    }
                }
            }
            $this->save();
        }
        return $this;
    }

    /**
     * Get quote items assigned to different quote addresses populated per item qty.
     * Based on result array we can display each item separately
     *
     * @return array
     */
    public function getQuoteShippingAddressesItems()
    {
        if ($this->_quoteShippingAddressesItems !== null) {
            return $this->_quoteShippingAddressesItems;
        }
        $items = [];
        $addresses = $this->getQuote()->getAllAddresses();
        foreach ($addresses as $address) {
            foreach ($address->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($item->getProduct()->getIsVirtual()) {
                    $items[] = $item;
                    continue;
                }
                if ($item->getQty() > 1) {
                    for ($i = 0, $n = $item->getQty(); $i < $n; $i++) {
                        if ($i == 0) {
                            $addressItem = $item;
                        } else {
                            $addressItem = clone $item;
                        }
                        $addressItem->setQty(1)->setCustomerAddressId($address->getCustomerAddressId())->save();
                        $items[] = $addressItem;
                    }
                } else {
                    $item->setCustomerAddressId($address->getCustomerAddressId());
                    $items[] = $item;
                }
            }
        }
        $this->_quoteShippingAddressesItems = $items;
        return $items;
    }

    /**
     * Remove item from address
     *
     * @param int $addressId
     * @param int $itemId
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function removeAddressItem($addressId, $itemId)
    {
        $address = $this->getQuote()->getAddressById($addressId);
        /* @var $address \Magento\Sales\Model\Quote\Address */
        if ($address) {
            $item = $address->getValidItemById($itemId);
            if ($item) {
                if ($item->getQty() > 1 && !$item->getProduct()->getIsVirtual()) {
                    $item->setQty($item->getQty() - 1);
                } else {
                    $address->removeItem($item->getId());
                }

                /**
                 * Require shipping rate recollect
                 */
                $address->setCollectShippingRates((bool)$this->getCollectRatesFlag());

                if (count($address->getAllItems()) == 0) {
                    $address->isDeleted(true);
                }

                $quoteItem = $this->getQuote()->getItemById($item->getQuoteItemId());
                if ($quoteItem) {
                    $newItemQty = $quoteItem->getQty() - 1;
                    if ($newItemQty > 0 && !$item->getProduct()->getIsVirtual()) {
                        $quoteItem->setQty($quoteItem->getQty() - 1);
                    } else {
                        $this->getQuote()->removeItem($quoteItem->getId());
                    }
                }
                $this->save();
            }
        }
        return $this;
    }

    /**
     * Assign quote items to addresses and specify items qty
     *
     * array structure:
     * array(
     *      $quoteItemId => array(
     *          'qty'       => $qty,
     *          'address'   => $customerAddressId
     *      )
     * )
     *
     * @param array $info
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Model\Exception
     */
    public function setShippingItemsInformation($info)
    {
        if (is_array($info)) {
            $allQty = 0;
            $itemsInfo = [];
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $allQty += $data['qty'];
                    $itemsInfo[$quoteItemId] = $data;
                }
            }

            $maxQty = $this->helper->getMaximumQty();
            if ($allQty > $maxQty) {
                throw new \Magento\Framework\Model\Exception(
                    __('Maximum qty allowed for Shipping to multiple addresses is %1', $maxQty)
                );
            }
            $quote = $this->getQuote();
            $addresses = $quote->getAllShippingAddresses();
            foreach ($addresses as $address) {
                $quote->removeAddress($address->getId());
            }

            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $this->_addShippingItem($quoteItemId, $data);
                }
            }

            /**
             * Delete all not virtual quote items which are not added to shipping address
             * MultishippingQty should be defined for each quote item when it processed with _addShippingItem
             */
            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual() && !$_item->getParentItem() && !$_item->getMultishippingQty()
                ) {
                    $quote->removeItem($_item->getId());
                }
            }

            $billingAddress = $quote->getBillingAddress();
            if ($billingAddress) {
                $quote->removeAddress($billingAddress->getId());
            }

            $customerDefaultBillingId = $this->getCustomerDefaultBillingAddress();
            if ($customerDefaultBillingId) {
                $quote->getBillingAddress()->importCustomerAddressData(
                    $this->addressRepository->getById($customerDefaultBillingId)
                );
            }

            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual()) {
                    continue;
                }

                if (isset($itemsInfo[$_item->getId()]['qty'])) {
                    $qty = (int)$itemsInfo[$_item->getId()]['qty'];
                    if ($qty) {
                        $_item->setQty($qty);
                        $quote->getBillingAddress()->addItem($_item);
                    } else {
                        $_item->setQty(0);
                        $quote->removeItem($_item->getId());
                    }
                }
            }

            $this->save();
            $this->_eventManager->dispatch('checkout_type_multishipping_set_shipping_items', ['quote' => $quote]);
        }
        return $this;
    }

    /**
     * Add quote item to specific shipping address based on customer address id
     *
     * @param int $quoteItemId
     * @param array $data array('qty'=>$qty, 'address'=>$customerAddressId)
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected function _addShippingItem($quoteItemId, $data)
    {
        $qty = isset($data['qty']) ? (int)$data['qty'] : 1;
        //$qty       = $qty > 0 ? $qty : 1;
        $addressId = isset($data['address']) ? $data['address'] : false;
        $quoteItem = $this->getQuote()->getItemById($quoteItemId);

        if ($addressId && $quoteItem) {
            /**
             * Skip item processing if qty 0
             */
            if ($qty === 0) {
                return $this;
            }
            $quoteItem->setMultishippingQty((int)$quoteItem->getMultishippingQty() + $qty);
            $quoteItem->setQty($quoteItem->getMultishippingQty());
            try {
                $address = $this->addressRepository->getById($addressId);
            } catch (\Exception $e) {
            }
            if (isset($address)) {
                if (!($quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId()))) {
                    $quoteAddress = $this->_addressFactory->create()->importCustomerAddressData($address);
                    $this->getQuote()->addShippingAddress($quoteAddress);
                }

                $quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId());
                $quoteAddressItem = $quoteAddress->getItemByQuoteItemId($quoteItemId);
                if ($quoteAddressItem) {
                    $quoteAddressItem->setQty((int)($quoteAddressItem->getQty() + $qty));
                } else {
                    $quoteAddress->addItem($quoteItem, $qty);
                }
                /**
                 * Require shipping rate recollect
                 */
                $quoteAddress->setCollectShippingRates((bool)$this->getCollectRatesFlag());
            }
        }
        return $this;
    }

    /**
     * Reimport customer address info to quote shipping address
     *
     * @param int $addressId customer address id
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function updateQuoteCustomerShippingAddress($addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (\Exception $e) {
            //
        }
        if (isset($address)) {
            $this->getQuote()->getShippingAddressByCustomerAddressId(
                $addressId
            )->setCollectShippingRates(
                true
            )->importCustomerAddressData(
                $address
            )->collectTotals();
            $this->quoteRepository->save($this->getQuote());
        }

        return $this;
    }

    /**
     * Reimport customer billing address to quote
     *
     * @param int $addressId customer address id
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function setQuoteCustomerBillingAddress($addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (\Exception $e) {
            //
        }
        if (isset($address)) {
            $this->getQuote()->getBillingAddress($addressId)->importCustomerAddressData($address)->collectTotals();
            $this->getQuote()->collectTotals();
            $this->quoteRepository->save($this->getQuote());
        }

        return $this;
    }

    /**
     * Assign shipping methods to addresses
     *
     * @param  array $methods
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Model\Exception
     */
    public function setShippingMethods($methods)
    {
        $addresses = $this->getQuote()->getAllShippingAddresses();
        foreach ($addresses as $address) {
            if (isset($methods[$address->getId()])) {
                $address->setShippingMethod($methods[$address->getId()]);
            } elseif (!$address->getShippingMethod()) {
                throw new \Magento\Framework\Model\Exception(__('Please select shipping methods for all addresses.'));
            }
        }
        $this->save();
        return $this;
    }

    /**
     * Set payment method info to quote payment
     *
     * @param array $payment
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Model\Exception
     */
    public function setPaymentMethod($payment)
    {
        if (!isset($payment['method'])) {
            throw new \Magento\Framework\Model\Exception(__('Payment method is not defined'));
        }
        if (!$this->paymentSpecification->isSatisfiedBy($payment['method'])) {
            throw new \Magento\Framework\Model\Exception(__('The requested Payment Method is not available for multishipping.'));
        }
        $quote = $this->getQuote();
        $quote->getPayment()->importData($payment);
        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setTotalsCollectedFlag(false)->collectTotals();
        }
        $this->quoteRepository->save($quote);
        return $this;
    }

    /**
     * Prepare order based on quote address
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\Sales\Model\Order
     * @throws  \Magento\Checkout\Exception
     */
    protected function _prepareOrder(\Magento\Sales\Model\Quote\Address $address)
    {
        $quote = $this->getQuote();
        $quote->unsReservedOrderId();
        $quote->reserveOrderId();
        $quote->collectTotals();

        $order = $this->_quote->addressToOrder($address);
        $order->setQuote($quote);
        $order->setBillingAddress($this->_quote->addressToOrderAddress($quote->getBillingAddress()));

        if ($address->getAddressType() == 'billing') {
            $order->setIsVirtual(1);
        } else {
            $order->setShippingAddress($this->_quote->addressToOrderAddress($address));
        }

        $order->setPayment($this->_quote->paymentToOrderPayment($quote->getPayment()));
        if ($this->priceCurrency->round($address->getGrandTotal()) == 0) {
            $order->getPayment()->setMethod('free');
        }

        foreach ($address->getAllItems() as $item) {
            $_quoteItem = $item->getQuoteItem();
            if (!$_quoteItem) {
                throw new \Magento\Checkout\Exception(__('Item not found or already ordered'));
            }
            $item->setProductType(
                $_quoteItem->getProductType()
            )->setProductOptions(
                $_quoteItem->getProduct()->getTypeInstance()->getOrderOptions($_quoteItem->getProduct())
            );
            $orderItem = $this->_quote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        return $order;
    }

    /**
     * Validate quote data
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _validate()
    {
        $quote = $this->getQuote();

        /** @var $paymentMethod \Magento\Payment\Model\Method\AbstractMethod */
        $paymentMethod = $quote->getPayment()->getMethodInstance();
        if (!$paymentMethod->isAvailable($quote)) {
            throw new \Magento\Framework\Model\Exception(__('Please specify a payment method.'));
        }

        $addresses = $quote->getAllShippingAddresses();
        foreach ($addresses as $address) {
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                throw new \Magento\Framework\Model\Exception(__('Please check shipping addresses information.'));
            }
            $method = $address->getShippingMethod();
            $rate = $address->getShippingRateByCode($method);
            if (!$method || !$rate) {
                throw new \Magento\Framework\Model\Exception(__('Please specify shipping methods for all addresses.'));
            }
        }
        $addressValidation = $quote->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            throw new \Magento\Framework\Model\Exception(__('Please check billing address information.'));
        }
        return $this;
    }

    /**
     * Create orders per each quote address
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @throws \Exception
     */
    public function createOrders()
    {
        $orderIds = [];
        $this->_validate();
        $shippingAddresses = $this->getQuote()->getAllShippingAddresses();
        $orders = [];

        if ($this->getQuote()->hasVirtualItems()) {
            $shippingAddresses[] = $this->getQuote()->getBillingAddress();
        }

        try {
            foreach ($shippingAddresses as $address) {
                $order = $this->_prepareOrder($address);

                $orders[] = $order;
                $this->_eventManager->dispatch(
                    'checkout_type_multishipping_create_orders_single',
                    ['order' => $order, 'address' => $address]
                );
            }

            foreach ($orders as $order) {
                $order->place();
                $order->save();
                if ($order->getCanSendNewEmailFlag()) {
                    $this->orderSender->send($order);
                }
                $orderIds[$order->getId()] = $order->getIncrementId();
            }

            $this->_session->setOrderIds($orderIds);
            $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId());

            $this->getQuote()->setIsActive(false);
            $this->quoteRepository->save($this->getQuote());

            $this->_eventManager->dispatch(
                'checkout_submit_all_after',
                ['orders' => $orders, 'quote' => $this->getQuote()]
            );

            return $this;
        } catch (\Exception $e) {
            $this->_eventManager->dispatch('checkout_multishipping_refund_all', ['orders' => $orders]);
            throw $e;
        }
    }

    /**
     * Collect quote totals and save quote object
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function save()
    {
        $this->getQuote()->collectTotals();
        $this->quoteRepository->save($this->getQuote());
        return $this;
    }

    /**
     * Specify BEGIN state in checkout session whot allow reinit multishipping checkout
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    public function reset()
    {
        $this->getCheckoutSession()->setCheckoutState(\Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN);
        return $this;
    }

    /**
     * Check if quote amount is allowed for multishipping checkout
     *
     * @return bool
     */
    public function validateMinimumAmount()
    {
        return !($this->_scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && $this->_scopeConfig->isSetFlag(
            'sales/minimum_order/multi_address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && !$this->getQuote()->validateMinimumAmount());
    }

    /**
     * Get notification message for case when multishipping checkout is not allowed
     *
     * @return string
     */
    public function getMinimumAmountDescription()
    {
        $descr = $this->_scopeConfig->getValue(
            'sales/minimum_order/multi_address_description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($descr)) {
            $descr = $this->_scopeConfig->getValue(
                'sales/minimum_order/description',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $descr;
    }

    /**
     * @return string
     */
    public function getMinimumAmountError()
    {
        $error = $this->_scopeConfig->getValue(
            'sales/minimum_order/multi_address_error_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($error)) {
            $error = $this->_scopeConfig->getValue(
                'sales/minimum_order/error_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $error;
    }

    /**
     * Get order IDs created during checkout
     *
     * @param bool $asAssoc
     * @return array
     */
    public function getOrderIds($asAssoc = false)
    {
        $idsAssoc = $this->_session->getOrderIds();
        return $asAssoc ? $idsAssoc : array_keys($idsAssoc);
    }

    /**
     * Retrieve customer default billing address
     *
     * @return int|null
     */
    public function getCustomerDefaultBillingAddress()
    {
        $defaultAddressId = $this->getCustomer()->getDefaultBilling();
        return $this->getDefaultAddressByDataKey('customer_default_billing_address', $defaultAddressId);
    }

    /**
     * Retrieve customer default shipping address
     *
     * @return int|null
     */
    public function getCustomerDefaultShippingAddress()
    {
        $defaultAddressId = $this->getCustomer()->getDefaultShipping();
        return $this->getDefaultAddressByDataKey('customer_default_shipping_address', $defaultAddressId);
    }

    /**
     * Retrieve customer default address by data key
     *
     * @param string $key
     * @param string|null $defaultAddressIdFromCustomer
     * @return int|null
     */
    private function getDefaultAddressByDataKey($key, $defaultAddressIdFromCustomer)
    {
        $addressId = $this->getData($key);
        if (is_null($addressId)) {
            $addressId = $defaultAddressIdFromCustomer;
            if (!$addressId) {
                /** Default address is not available, try to find any customer address */
                $filter =  $this->filterBuilder->setField('parent_id')
                    ->setValue($this->getCustomer()->getId())
                    ->setConditionType('eq')
                    ->create();
                $addresses = (array)($this->addressRepository->getList(
                    $this->searchCriteriaBuilder->addFilter([$filter])->create()
                )->getItems());
                if ($addresses) {
                    $address = reset($addresses);
                    $addressId = $address->getId();
                }
            }
            $this->setData($key, $addressId);
        }

        return $addressId;
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        $checkout = $this->getData('checkout_session');
        if (is_null($checkout)) {
            $checkout = $this->_checkoutSession;
            $this->setData('checkout_session', $checkout);
        }
        return $checkout;
    }

    /**
     * Retrieve quote model
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Retrieve quote items
     *
     * @return \Magento\Sales\Model\Quote\Item[]
     */
    public function getQuoteItems()
    {
        return $this->getQuote()->getAllItems();
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Retrieve customer object
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->_customerSession->getCustomerDataObject();
    }
}
