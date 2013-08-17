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
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout model
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Model_Type_Multishipping extends Mage_Checkout_Model_Type_Abstract
{
    /**
     * Quote shipping addresses items cache
     *
     * @var array
     */
    protected $_quoteShippingAddressesItems;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_init();
    }

    /**
     * Initialize multishipping checkout.
     * Split virtual/not virtual items between default billing/shipping addresses
     *
     * @return Mage_Checkout_Model_Type_Multishipping
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

        if ($this->getCheckoutSession()->getCheckoutState() === Mage_Checkout_Model_Session::CHECKOUT_STATE_BEGIN) {
            $this->getCheckoutSession()->setCheckoutState(true);
            /**
             * Remove all addresses
             */
            $addresses  = $quote->getAllAddresses();
            foreach ($addresses as $address) {
                $quote->removeAddress($address->getId());
            }

            if ($defaultShipping = $this->getCustomerDefaultShippingAddress()) {
                $quote->getShippingAddress()->importCustomerAddress($defaultShipping);

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

            if ($this->getCustomerDefaultBillingAddress()) {
                $quote->getBillingAddress()
                    ->importCustomerAddress($this->getCustomerDefaultBillingAddress());
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
        $items = array();
        $addresses  = $this->getQuote()->getAllAddresses();
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
                        $addressItem->setQty(1)
                            ->setCustomerAddressId($address->getCustomerAddressId())
                            ->save();
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
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function removeAddressItem($addressId, $itemId)
    {
        $address = $this->getQuote()->getAddressById($addressId);
        /* @var $address Mage_Sales_Model_Quote_Address */
        if ($address) {
            $item = $address->getValidItemById($itemId);
            if ($item) {
                if ($item->getQty()>1 && !$item->getProduct()->getIsVirtual()) {
                    $item->setQty($item->getQty()-1);
                } else {
                    $address->removeItem($item->getId());
                }

                /**
                 * Require shiping rate recollect
                 */
                $address->setCollectShippingRates((boolean) $this->getCollectRatesFlag());

                if (count($address->getAllItems()) == 0) {
                    $address->isDeleted(true);
                }

                if ($quoteItem = $this->getQuote()->getItemById($item->getQuoteItemId())) {
                    $newItemQty = $quoteItem->getQty()-1;
                    if ($newItemQty > 0 && !$item->getProduct()->getIsVirtual()) {
                        $quoteItem->setQty($quoteItem->getQty()-1);
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
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setShippingItemsInformation($info)
    {
        if (is_array($info)) {
            $allQty = 0;
            $itemsInfo = array();
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $allQty += $data['qty'];
                    $itemsInfo[$quoteItemId] = $data;
                }
            }

            $maxQty = (int)Mage::getStoreConfig('shipping/option/checkout_multiple_maximum_qty');
            if ($allQty > $maxQty) {
                Mage::throwException(Mage::helper('Mage_Checkout_Helper_Data')->__('Maximum qty allowed for Shipping to multiple addresses is %s', $maxQty));
            }
            $quote = $this->getQuote();
            $addresses  = $quote->getAllShippingAddresses();
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
                if (!$_item->getProduct()->getIsVirtual() &&
                    !$_item->getParentItem() &&
                    !$_item->getMultishippingQty()
                ) {
                    $quote->removeItem($_item->getId());
                }
            }

            if ($billingAddress = $quote->getBillingAddress()) {
                $quote->removeAddress($billingAddress->getId());
            }

            if ($customerDefaultBilling = $this->getCustomerDefaultBillingAddress()) {
                $quote->getBillingAddress()->importCustomerAddress($customerDefaultBilling);
            }

            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual()) {
                    continue;
                }

                if (isset($itemsInfo[$_item->getId()]['qty'])) {
                    if ($qty = (int)$itemsInfo[$_item->getId()]['qty']) {
                        $_item->setQty($qty);
                        $quote->getBillingAddress()->addItem($_item);
                    } else {
                        $_item->setQty(0);
                        $quote->removeItem($_item->getId());
                    }
                 }

            }

            $this->save();
            Mage::dispatchEvent('checkout_type_multishipping_set_shipping_items', array('quote'=>$quote));
        }
        return $this;
    }

    /**
     * Add quote item to specific shipping address based on customer address id
     *
     * @param int $quoteItemId
     * @param array $data array('qty'=>$qty, 'address'=>$customerAddressId)
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    protected function _addShippingItem($quoteItemId, $data)
    {
        $qty       = isset($data['qty']) ? (int) $data['qty'] : 1;
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
            $quoteItem->setMultishippingQty((int)$quoteItem->getMultishippingQty()+$qty);
            $quoteItem->setQty($quoteItem->getMultishippingQty());
            $address = $this->getCustomer()->getAddressById($addressId);
            if ($address->getId()) {
                if (!$quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId())) {
                    $quoteAddress = Mage::getModel('Mage_Sales_Model_Quote_Address')->importCustomerAddress($address);
                    $this->getQuote()->addShippingAddress($quoteAddress);
                }

                $quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId());
                if ($quoteAddressItem = $quoteAddress->getItemByQuoteItemId($quoteItemId)) {
                    $quoteAddressItem->setQty((int)($quoteAddressItem->getQty()+$qty));
                } else {
                    $quoteAddress->addItem($quoteItem, $qty);
                }
                /**
                 * Require shiping rate recollect
                 */
                $quoteAddress->setCollectShippingRates((boolean) $this->getCollectRatesFlag());
            }
        }
        return $this;
    }

    /**
     * Reimport customer address info to quote shipping address
     *
     * @param int $addressId customer address id
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function updateQuoteCustomerShippingAddress($addressId)
    {
        if ($address = $this->getCustomer()->getAddressById($addressId)) {
            $this->getQuote()->getShippingAddressByCustomerAddressId($addressId)
                ->setCollectShippingRates(true)
                ->importCustomerAddress($address)
                ->collectTotals();
            $this->getQuote()->save();
        }
        return $this;
    }

    /**
     * Reimport customer billing address to quote
     *
     * @param int $addressId customer address id
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setQuoteCustomerBillingAddress($addressId)
    {
        if ($address = $this->getCustomer()->getAddressById($addressId)) {
            $this->getQuote()->getBillingAddress($addressId)
                ->importCustomerAddress($address)
                ->collectTotals();
            $this->getQuote()->collectTotals()->save();
        }
        return $this;
    }

    /**
     * Assign shipping methods to addresses
     *
     * @param  array $methods
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setShippingMethods($methods)
    {
        $addresses = $this->getQuote()->getAllShippingAddresses();
        foreach ($addresses as $address) {
            if (isset($methods[$address->getId()])) {
                $address->setShippingMethod($methods[$address->getId()]);
            } elseif (!$address->getShippingMethod()) {
                Mage::throwException(Mage::helper('Mage_Checkout_Helper_Data')->__('Please select shipping methods for all addresses.'));
            }
        }
        $this->save();
        return $this;
    }

    /**
     * Set payment method info to quote payment
     *
     * @param array $payment
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function setPaymentMethod($payment)
    {
        if (!isset($payment['method'])) {
            Mage::throwException(Mage::helper('Mage_Checkout_Helper_Data')->__('Payment method is not defined'));
        }
        $quote = $this->getQuote();
        $quote->getPayment()->importData($payment);
        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setTotalsCollectedFlag(false)->collectTotals();
        }
        $quote->save();
        return $this;
    }

    /**
     * Prepare order based on quote address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Order
     * @throws  Mage_Checkout_Exception
     */
    protected function _prepareOrder(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $this->getQuote();
        $quote->unsReservedOrderId();
        $quote->reserveOrderId();
        $quote->collectTotals();

        $convertQuote = Mage::getSingleton('Mage_Sales_Model_Convert_Quote');
        $order = $convertQuote->addressToOrder($address);
        $order->setQuote($quote);
        $order->setBillingAddress(
            $convertQuote->addressToOrderAddress($quote->getBillingAddress())
        );

        if ($address->getAddressType() == 'billing') {
            $order->setIsVirtual(1);
        } else {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($address));
        }

        $order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
        if (Mage::app()->getStore()->roundPrice($address->getGrandTotal()) == 0) {
            $order->getPayment()->setMethod('free');
        }

        foreach ($address->getAllItems() as $item) {
            $_quoteItem = $item->getQuoteItem();
            if (!$_quoteItem) {
                throw new Mage_Checkout_Exception(Mage::helper('Mage_Checkout_Helper_Data')->__('Item not found or already ordered'));
            }
            $item->setProductType($_quoteItem->getProductType())
                ->setProductOptions(
                    $_quoteItem->getProduct()->getTypeInstance()->getOrderOptions($_quoteItem->getProduct())
                );
            $orderItem = $convertQuote->itemToOrderItem($item);
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
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    protected function _validate()
    {
        $quote = $this->getQuote();
        if (!$quote->getIsMultiShipping()) {
            Mage::throwException(Mage::helper('Mage_Checkout_Helper_Data')->__('Invalid checkout type'));
        }

        /** @var $paymentMethod Mage_Payment_Model_Method_Abstract */
        $paymentMethod = $quote->getPayment()->getMethodInstance();
        if (!empty($paymentMethod) && !$paymentMethod->isAvailable($quote)) {
            Mage::throwException(Mage::helper('Mage_Checkout_Helper_Data')->__('Please specify a payment method.'));
        }

        $addresses = $quote->getAllShippingAddresses();
        foreach ($addresses as $address) {
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException(Mage::helper('Mage_Checkout_Helper_Data')->__('Please check shipping addresses information.'));
            }
            $method= $address->getShippingMethod();
            $rate  = $address->getShippingRateByCode($method);
            if (!$method || !$rate) {
                Mage::throwException(Mage::helper('Mage_Checkout_Helper_Data')->__('Please specify shipping methods for all addresses.'));
            }
        }
        $addressValidation = $quote->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            Mage::throwException(Mage::helper('Mage_Checkout_Helper_Data')->__('Please check billing address information.'));
        }
        return $this;
    }

    /**
     * Create orders per each quote address
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function createOrders()
    {
        $orderIds = array();
        $this->_validate();
        $shippingAddresses = $this->getQuote()->getAllShippingAddresses();
        $orders = array();

        if ($this->getQuote()->hasVirtualItems()) {
            $shippingAddresses[] = $this->getQuote()->getBillingAddress();
        }

        try {
            foreach ($shippingAddresses as $address) {
                $order = $this->_prepareOrder($address);

                $orders[] = $order;
                Mage::dispatchEvent(
                    'checkout_type_multishipping_create_orders_single',
                    array('order'=>$order, 'address'=>$address)
                );
            }

            foreach ($orders as $order) {
                $order->place();
                $order->save();
                if ($order->getCanSendNewEmailFlag()){
                    $order->sendNewOrderEmail();
                }
                $orderIds[$order->getId()] = $order->getIncrementId();
            }

            Mage::getSingleton('Mage_Core_Model_Session')->setOrderIds($orderIds);
            Mage::getSingleton('Mage_Checkout_Model_Session')->setLastQuoteId($this->getQuote()->getId());

            $this->getQuote()
                ->setIsActive(false)
                ->save();

            Mage::dispatchEvent('checkout_submit_all_after', array('orders' => $orders, 'quote' => $this->getQuote()));

            return $this;
        } catch (Exception $e) {
            Mage::dispatchEvent('checkout_multishipping_refund_all', array('orders' => $orders));
            throw $e;
        }
    }

    /**
     * Collect quote totals and save quote object
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function save()
    {
        $this->getQuote()->collectTotals()
            ->save();
        return $this;
    }

    /**
     * Specify BEGIN state in checkout session whot allow reinit multishipping checkout
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function reset()
    {
        $this->getCheckoutSession()->setCheckoutState(Mage_Checkout_Model_Session::CHECKOUT_STATE_BEGIN);
        return $this;
    }

    /**
     * Check if quote amount is allowed for multishipping checkout
     *
     * @return bool
     */
    public function validateMinimumAmount()
    {
        return !(Mage::getStoreConfigFlag('sales/minimum_order/active')
            && Mage::getStoreConfigFlag('sales/minimum_order/multi_address')
            && !$this->getQuote()->validateMinimumAmount());
    }

    /**
     * Get notification message for case when multishipping checkout is not allowed
     *
     * @return string
     */
    public function getMinimumAmountDescription()
    {
        $descr = Mage::getStoreConfig('sales/minimum_order/multi_address_description');
        if (empty($descr)) {
            $descr = Mage::getStoreConfig('sales/minimum_order/description');
        }
        return $descr;
    }

    public function getMinimumAmountError()
    {
        $error = Mage::getStoreConfig('sales/minimum_order/multi_address_error_message');
        if (empty($error)) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
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
        $idsAssoc = Mage::getSingleton('Mage_Core_Model_Session')->getOrderIds();
        return $asAssoc ? $idsAssoc : array_keys($idsAssoc);
    }
}
