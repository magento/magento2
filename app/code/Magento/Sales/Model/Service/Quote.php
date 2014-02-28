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

namespace Magento\Sales\Model\Service;

use Magento\Customer\Service\V1\CustomerServiceInterface;
use Magento\Customer\Service\V1\CustomerAddressServiceInterface;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\Dto\AddressBuilder;
use Magento\Customer\Service\V1\Dto\Customer as CustomerDto;
use Magento\Customer\Service\V1\Dto\Response\CreateCustomerAccountResponse;

/**
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
    protected $_orderData = array();

    /**
     * Order that may be created during submission
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order = null;

    /**
     * If it is true, quote will be inactivate after submitting order or nominal items
     *
     * @var bool
     */
    protected $_shouldInactivateQuote = true;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Core\Model\Resource\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var CustomerServiceInterface
     */
    protected $_customerService;

    /**
     * @var CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * @var CustomerAddressServiceInterface
     */
    protected $_customerAddressService;

    /**
     * @var AddressBuilder
     */
    protected $_customerAddressBuilder;

    /**
     * @var CreateCustomerAccountResponse
     */
    protected $_createCustomerResponse;

    /**
     * Class constructor
     *
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\Quote $quote
     * @param \Magento\Sales\Model\Convert\QuoteFactory $convertQuoteFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Model\Resource\TransactionFactory $transactionFactory
     * @param CustomerServiceInterface $customerService
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param CustomerAddressServiceInterface $customerAddressService
     * @param AddressBuilder $customerAddressBuilder
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\Quote $quote,
        \Magento\Sales\Model\Convert\QuoteFactory $convertQuoteFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Model\Resource\TransactionFactory $transactionFactory,
        CustomerServiceInterface $customerService,
        CustomerAccountServiceInterface $customerAccountService,
        CustomerAddressServiceInterface $customerAddressService,
        AddressBuilder $customerAddressBuilder
    ) {
        $this->_eventManager = $eventManager;
        $this->_quote = $quote;
        $this->_convertor = $convertQuoteFactory->create();
        $this->_customerSession = $customerSession;
        $this->_transactionFactory = $transactionFactory;
        $this->_customerService = $customerService;
        $this->_customerAccountService = $customerAccountService;
        $this->_customerAddressService = $customerAddressService;
        $this->_customerAddressBuilder = $customerAddressBuilder;
    }

    /**
     * Quote convertor declaration
     *
     * @param   \Magento\Sales\Model\Convert\Quote $convertor
     * @return  \Magento\Sales\Model\Service\Quote
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
     * @return \Magento\Sales\Model\Service\Quote
     */
    public function setOrderData(array $data)
    {
        $this->_orderData = $data;
        return $this;
    }

    /**
     * Submit the quote. Quote submit process will create the order based on quote data
     *
     * @deprecated
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function submitOrder()
    {
        $this->_deleteNominalItems();
        $this->_validate();
        $quote = $this->_quote;
        $isVirtual = $quote->isVirtual();

        $transaction = $this->_transactionFactory->create();
        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
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
            $order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()->getCustomerAddress());
        }
        if (!$isVirtual) {
            $order->setShippingAddress($this->_convertor->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()->getCustomerAddress());
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

        $order->setQuote($quote);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));

        /**
         * We can use configuration data for declare new order status
         */
        $this->_eventManager->dispatch('checkout_type_onepage_save_order', array(
            'order' => $order,
            'quote' => $quote
        ));
        $this->_eventManager->dispatch('sales_model_service_quote_submit_before', array(
            'order' => $order,
            'quote' => $quote
        ));
        try {
            $transaction->save();
            $this->_inactivateQuote();
            $this->_eventManager->dispatch('sales_model_service_quote_submit_success', array(
                'order' => $order,
                'quote' => $quote
            ));
        } catch (\Exception $e) {
            if (!$this->_customerSession->isLoggedIn()) {
                // reset customer ID's on exception, because customer not saved
                $quote->getCustomer()->setId(null);
            }

            //reset order ID's on exception, because order not saved
            $order->setId(null);
            /** @var $item \Magento\Sales\Model\Order\Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            $this->_eventManager->dispatch('sales_model_service_quote_submit_failure', array(
                    'order' => $order,
                    'quote' => $quote
                ));
            throw $e;
        }
        $this->_eventManager->dispatch('sales_model_service_quote_submit_after', array(
                'order' => $order,
                'quote' => $quote
            ));
        $this->_order = $order;
        return $order;
    }

    /**
     * Submit the quote. Quote submit process will create the order based on quote data
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function submitOrderWithDto()
    {
        $this->_deleteNominalItems();
        $this->_validate();
        $quote = $this->_quote;
        $isVirtual = $quote->isVirtual();

        $transaction = $this->_transactionFactory->create();

        $originalCustomerDto = null;
        $customerDto = null;
        if (!$quote->getCustomerIsGuest()) {
            $customerDto = $quote->getCustomerData();
            $addresses = $quote->getCustomerAddressData();
            if ($customerDto->getCustomerId()) {
                //cache the original customer data for rollback if needed
                $originalCustomerDto = $this->_customerService->getCustomer($customerDto->getCustomerId());
                $originalAddresses = $this->_customerAddressService->getAddresses($customerDto->getCustomerId());
                //Save updated data
                $this->_customerService->saveCustomer($customerDto);
                $this->_customerAddressService->saveAddresses($customerDto->getCustomerId(), $addresses);
            } else { //for new customers
                $this->_createCustomerResponse = $this->_customerAccountService->createAccount(
                    $customerDto,
                    $addresses,
                    null,
                    '',
                    '',
                    $quote->getStoreId()
                );
                $customerDto = $this->_customerService->getCustomer($this->_createCustomerResponse->getCustomerId());
                $addresses = $this->_customerAddressService->getAddresses(
                    $this->_createCustomerResponse->getCustomerId()
                );
                //Update quote address information
                foreach ($addresses as $address) {
                    if ($address->isDefaultBilling()) {
                        $quote->getBillingAddress()->setCustomerAddressData($address);
                    } else if ($address->isDefaultShipping()) {
                        $quote->getShippingAddress()->setCustomerAddressData($address);
                    }
                }
                if ($quote->getShippingAddress() && $quote->getShippingAddress()->getSameAsBilling()) {
                    $quote->getShippingAddress()->setCustomerAddressData(
                        $quote->getBillingAddress()->getCustomerAddressData());
                }
            }

            $quote->setCustomerData($customerDto)->setCustomerAddressData($addresses);
        }
        $transaction->addObject($quote);

        $quote->reserveOrderId();
        if ($isVirtual) {
            $order = $this->_convertor->addressToOrder($quote->getBillingAddress());
        } else {
            $order = $this->_convertor->addressToOrder($quote->getShippingAddress());
        }
        $order->setBillingAddress($this->_convertor->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddressData()) {
            $order->getBillingAddress()->setCustomerAddressData($quote->getBillingAddress()->getCustomerAddressData());
        }
        if (!$isVirtual) {
            $order->setShippingAddress($this->_convertor->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddressData()) {
                $order->getShippingAddress()->setCustomerAddressData(
                    $quote->getShippingAddress()->getCustomerAddressData());
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

        if ($customerDto) {
            $order->setCustomerId($customerDto->getCustomerId());
        }
        $order->setQuote($quote);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));

        /**
         * We can use configuration data for declare new order status
         */
        $this->_eventManager->dispatch('checkout_type_onepage_save_order', array(
            'order' => $order,
            'quote' => $quote
        ));
        $this->_eventManager->dispatch('sales_model_service_quote_submit_before', array(
            'order' => $order,
            'quote' => $quote
        ));
        try {
            $transaction->save();
            $this->_inactivateQuote();
            $this->_eventManager->dispatch('sales_model_service_quote_submit_success', array(
                'order' => $order,
                'quote' => $quote
            ));
        } catch (\Exception $e) {
            if ($originalCustomerDto) { //Restore original customer data if existing customer was updated
                $this->_customerService->saveCustomer($originalCustomerDto);
                $this->_customerAddressService->saveAddresses($customerDto->getCustomerId(), $originalAddresses);
            } else if ($customerDto->getCustomerId()) { // Delete if new customer created
                $this->_customerService->deleteCustomer($customerDto->getCustomerId());
                $order->setCustomerId(null);
                $quote->setCustomerData(new CustomerDto([]));
            }

            //reset order ID's on exception, because order not saved
            $order->setId(null);
            /** @var $item \Magento\Sales\Model\Order\Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            $this->_eventManager->dispatch('sales_model_service_quote_submit_failure', array(
                'order' => $order,
                'quote' => $quote
            ));
            throw $e;
        }
        $this->_eventManager->dispatch('sales_model_service_quote_submit_after', array(
            'order' => $order,
            'quote' => $quote
        ));
        $this->_order = $order;
        return $order;
    }

    /**
     * Submit nominal items
     *
     * @return array
     */
    public function submitNominalItems()
    {
        $this->_validate();
        $this->_eventManager->dispatch('sales_model_service_quote_submit_nominal_items', ['quote' => $this->_quote]);
        $this->_inactivateQuote();
        $this->_deleteNominalItems();
    }

    /**
     * Submit all available items
     * All created items will be set to the object
     */
    public function submitAll()
    {
        // don't allow submitNominalItems() to inactivate quote
        $inactivateQuoteOld = $this->_shouldInactivateQuote;
        $this->_shouldInactivateQuote = false;
        try {
            $this->submitNominalItems();
            $this->_shouldInactivateQuote = $inactivateQuoteOld;
        } catch (\Exception $e) {
            $this->_shouldInactivateQuote = $inactivateQuoteOld;
            throw $e;
        }
        // no need to submit the order if there are no normal items remained
        if (!$this->_quote->getAllVisibleItems()) {
            $this->_inactivateQuote();
            return;
        }
        $this->submitOrder();
    }

    /**
     * Submit all available items
     * All created items will be set to the object
     */
    public function submitAllWithDto()
    {
        // don't allow submitNominalItems() to inactivate quote
        $inactivateQuoteOld = $this->_shouldInactivateQuote;
        $this->_shouldInactivateQuote = false;
        try {
            $this->submitNominalItems();
            $this->_shouldInactivateQuote = $inactivateQuoteOld;
        } catch (\Exception $e) {
            $this->_shouldInactivateQuote = $inactivateQuoteOld;
            throw $e;
        }
        // no need to submit the order if there are no normal items remained
        if (!$this->_quote->getAllVisibleItems()) {
            $this->_inactivateQuote();
            return;
        }
        $this->submitOrderWithDto();
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
     * Get response when CustomerAccountService was invoked to create a new customer account
     *
     * @return CreateCustomerAccountResponse
     */
    public function getCreateCustomerResponse()
    {
        return $this->_createCustomerResponse;
    }

    /**
     * Inactivate quote
     *
     * @return \Magento\Sales\Model\Service\Quote
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
     * @return \Magento\Sales\Model\Service\Quote
     * @throws \Magento\Core\Exception
     */
    protected function _validate()
    {
        if (!$this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                throw new \Magento\Core\Exception(
                    __('Please check the shipping address information. %1', implode(' ', $addressValidation))
                );
            }
            $method= $address->getShippingMethod();
            $rate  = $address->getShippingRateByCode($method);
            if (!$this->getQuote()->isVirtual() && (!$method || !$rate)) {
                throw new \Magento\Core\Exception(__('Please specify a shipping method.'));
            }
        }

        $addressValidation = $this->getQuote()->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            throw new \Magento\Core\Exception(
                __('Please check the billing address information. %1', implode(' ', $addressValidation))
            );
        }

        if (!($this->getQuote()->getPayment()->getMethod())) {
            throw new \Magento\Core\Exception(__('Please select a valid payment method.'));
        }

        return $this;
    }

    /**
     * Get rid of all nominal items
     */
    protected function _deleteNominalItems()
    {
        foreach ($this->_quote->getAllVisibleItems() as $item) {
            if ($item->isNominal()) {
                $item->isDeleted(true);
            }
        }
    }
}
