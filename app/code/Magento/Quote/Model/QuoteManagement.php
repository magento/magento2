<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class QuoteManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteManagement implements \Magento\Quote\Api\CartManagementInterface
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
     * @var OrderFactory
     */
    protected $orderFactory;

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
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModelFactory;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var array
     */
    private $addressesToSync = [];

    /**
     * @param EventManager $eventManager
     * @param QuoteValidator $quoteValidator
     * @param OrderFactory $orderFactory
     * @param OrderManagement $orderManagement
     * @param CustomerManagement $customerManagement
     * @param ToOrderConverter $quoteAddressToOrder
     * @param ToOrderAddressConverter $quoteAddressToOrderAddress
     * @param ToOrderItemConverter $quoteItemToOrderItem
     * @param ToOrderPaymentConverter $quotePaymentToOrderPayment
     * @param UserContextInterface $userContext
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerModelFactory
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\QuoteIdMaskFactory|null $quoteIdMaskFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface|null $addressRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EventManager $eventManager,
        QuoteValidator $quoteValidator,
        OrderFactory $orderFactory,
        OrderManagement $orderManagement,
        CustomerManagement $customerManagement,
        ToOrderConverter $quoteAddressToOrder,
        ToOrderAddressConverter $quoteAddressToOrderAddress,
        ToOrderItemConverter $quoteItemToOrderItem,
        ToOrderPaymentConverter $quotePaymentToOrderPayment,
        UserContextInterface $userContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory = null,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository = null
    ) {
        $this->eventManager = $eventManager;
        $this->quoteValidator = $quoteValidator;
        $this->orderFactory = $orderFactory;
        $this->orderManagement = $orderManagement;
        $this->customerManagement = $customerManagement;
        $this->quoteAddressToOrder = $quoteAddressToOrder;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
        $this->userContext = $userContext;
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->customerModelFactory = $customerModelFactory;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->accountManagement = $accountManagement;
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Quote\Model\QuoteIdMaskFactory::class);
        $this->addressRepository = $addressRepository ?: ObjectManager::getInstance()
            ->get(\Magento\Customer\Api\AddressRepositoryInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function createEmptyCart()
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $quote = $this->createAnonymousCart($storeId);

        $quote->setBillingAddress($this->quoteAddressFactory->create());
        $quote->setShippingAddress($this->quoteAddressFactory->create());

        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);

            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Cannot create quote'));
        }
        return $quote->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function createEmptyCartForCustomer($customerId)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $quote = $this->createCustomerCart($customerId, $storeId);

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Cannot create quote'));
        }
        return $quote->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function assignCustomer($cartId, $customerId, $storeId)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $customer = $this->customerRepository->getById($customerId);
        $customerModel = $this->customerModelFactory->create();

        if (!in_array($storeId, $customerModel->load($customerId)->getSharedStoreIds())) {
            throw new StateException(
                __('Cannot assign customer to the given cart. The cart belongs to different store.')
            );
        }
        if ($quote->getCustomerId()) {
            throw new StateException(
                __('Cannot assign customer to the given cart. The cart is not anonymous.')
            );
        }
        try {
            $this->quoteRepository->getForCustomer($customerId);
            throw new StateException(
                __('Cannot assign customer to the given cart. Customer already has active cart.')
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }

        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'quote_id');
        if ($quoteIdMask->getId()) {
            $quoteIdMask->delete();
        }
        $this->quoteRepository->save($quote);
        return true;
    }

    /**
     * Creates an anonymous cart.
     *
     * @param int $storeId
     * @return \Magento\Quote\Model\Quote Cart object.
     */
    protected function createAnonymousCart($storeId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($storeId);
        return $quote;
    }

    /**
     * Creates a cart for the currently logged-in customer.
     *
     * @param int $customerId
     * @param int $storeId
     * @return \Magento\Quote\Model\Quote Cart object.
     * @throws CouldNotSaveException The cart could not be created.
     */
    protected function createCustomerCart($customerId, $storeId)
    {
        try {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customer = $this->customerRepository->getById($customerId);
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->quoteFactory->create();
            $quote->setStoreId($storeId);
            $quote->setCustomer($customer);
            $quote->setCustomerIsGuest(0);
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function placeOrder($cartId, PaymentInterface $paymentMethod = null)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        if ($paymentMethod) {
            $paymentMethod->setChecks([
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
            ]);
            $quote->getPayment()->setQuote($quote);

            $data = $paymentMethod->getData();
            $quote->getPayment()->importData($data);
        }

        if ($quote->getCheckoutMethod() === self::METHOD_GUEST) {
            $quote->setCustomerId(null);
            $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
            $quote->setCustomerIsGuest(true);
            $quote->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        }

        $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quote]);

        $order = $this->submit($quote);

        if (null == $order) {
            throw new LocalizedException(
                __('An error occurred on the server. Please try to place the order again.')
            );
        }

        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());

        $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);
        return $order->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getCartForCustomer($customerId)
    {
        return $this->quoteRepository->getActiveForCustomer($customerId);
    }

    /**
     * Submit quote
     *
     * @param Quote $quote
     * @param array $orderData
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object|null
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function submit(QuoteEntity $quote, $orderData = [])
    {
        if (!$quote->getAllVisibleItems()) {
            $quote->setIsActive(false);
            return null;
        }

        return $this->submitQuote($quote, $orderData);
    }

    /**
     * @param Quote $quote
     * @return array
     */
    protected function resolveItems(QuoteEntity $quote)
    {
        $orderItems = [];
        foreach ($quote->getAllItems() as $quoteItem) {
            $itemId = $quoteItem->getId();

            if (!empty($orderItems[$itemId])) {
                continue;
            }

            $parentItemId = $quoteItem->getParentItemId();
            /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $parentItem */
            if ($parentItemId && !isset($orderItems[$parentItemId])) {
                $orderItems[$parentItemId] = $this->quoteItemToOrderItem->convert(
                    $quoteItem->getParentItem(),
                    ['parent_item' => null]
                );
            }
            $parentItem = isset($orderItems[$parentItemId]) ? $orderItems[$parentItemId] : null;
            $orderItems[$itemId] = $this->quoteItemToOrderItem->convert($quoteItem, ['parent_item' => $parentItem]);
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function submitQuote(QuoteEntity $quote, $orderData = [])
    {
        $order = $this->orderFactory->create();
        $this->quoteValidator->validateBeforeSubmit($quote);
        if (!$quote->getCustomerIsGuest()) {
            if ($quote->getCustomerId()) {
                $this->_prepareCustomerQuote($quote);
                $this->customerManagement->validateAddresses($quote);
            }
            $this->customerManagement->populateCustomerInfo($quote);
        }
        $addresses = [];
        $quote->reserveOrderId();
        if ($quote->isVirtual()) {
            $this->dataObjectHelper->mergeDataObjects(
                \Magento\Sales\Api\Data\OrderInterface::class,
                $order,
                $this->quoteAddressToOrder->convert($quote->getBillingAddress(), $orderData)
            );
        } else {
            $this->dataObjectHelper->mergeDataObjects(
                \Magento\Sales\Api\Data\OrderInterface::class,
                $order,
                $this->quoteAddressToOrder->convert($quote->getShippingAddress(), $orderData)
            );
            $shippingAddress = $this->quoteAddressToOrderAddress->convert(
                $quote->getShippingAddress(),
                [
                    'address_type' => 'shipping',
                    'email' => $quote->getCustomerEmail()
                ]
            );
            $shippingAddress->setData('quote_address_id', $quote->getShippingAddress()->getId());
            $addresses[] = $shippingAddress;
            $order->setShippingAddress($shippingAddress);
            $order->setShippingMethod($quote->getShippingAddress()->getShippingMethod());
        }
        $billingAddress = $this->quoteAddressToOrderAddress->convert(
            $quote->getBillingAddress(),
            [
                'address_type' => 'billing',
                'email' => $quote->getCustomerEmail()
            ]
        );
        $billingAddress->setData('quote_address_id', $quote->getBillingAddress()->getId());
        $addresses[] = $billingAddress;
        $order->setBillingAddress($billingAddress);
        $order->setAddresses($addresses);
        $order->setPayment($this->quotePaymentToOrderPayment->convert($quote->getPayment()));
        $order->setItems($this->resolveItems($quote));
        if ($quote->getCustomer()) {
            $order->setCustomerId($quote->getCustomer()->getId());
        }
        $order->setQuoteId($quote->getId());
        $order->setCustomerEmail($quote->getCustomerEmail());
        $order->setCustomerFirstname($quote->getCustomerFirstname());
        $order->setCustomerMiddlename($quote->getCustomerMiddlename());
        $order->setCustomerLastname($quote->getCustomerLastname());

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
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            if (!empty($this->addressesToSync)) {
                foreach ($this->addressesToSync as $addressId) {
                    $this->addressRepository->deleteById($addressId);
                }
            }
            $this->eventManager->dispatch(
                'sales_model_service_quote_submit_failure',
                [
                    'order'     => $order,
                    'quote'     => $quote,
                    'exception' => $e
                ]
            );
            throw $e;
        }
        return $order;
    }

    /**
     * Prepare quote for customer order submit
     *
     * @param Quote $quote
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareCustomerQuote($quote)
    {
        /** @var Quote $quote */
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $this->customerRepository->getById($quote->getCustomerId());
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();

        if ($shipping && !$shipping->getSameAsBilling()
            && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $shipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                //Make provided address as default shipping address
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
            }
            //save here new customer address
            $shippingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($shippingAddress);
            $quote->addCustomerAddress($shippingAddress);
            $shipping->setCustomerAddressData($shippingAddress);
            $this->addressesToSync[] = $shippingAddress->getId();
            $shipping->setCustomerAddressId($shippingAddress->getId());
        }

        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $billingAddress = $billing->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                //Make provided address as default shipping address
                if (!$hasDefaultShipping) {
                    //Make provided address as default shipping address
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
            }
            $billingAddress->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($billingAddress);
            $quote->addCustomerAddress($billingAddress);
            $billing->setCustomerAddressData($billingAddress);
            $this->addressesToSync[] = $billingAddress->getId();
            $billing->setCustomerAddressId($billingAddress->getId());
        }
        if ($shipping && !$shipping->getCustomerId() && !$hasDefaultBilling) {
            $shipping->setIsDefaultBilling(true);
        }
    }
}
