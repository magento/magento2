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
namespace Magento\RecurringPayment\Model;

/**
 * Sales implementation of recurring payments
 * Implements saving and managing payments
 *
 * @method int getCustomerId()
 * @method Payment setCustomerId(int $value)
 * @method int getStoreId()
 * @method Payment setStoreId(int $value)
 * @method string getMethodCode()
 * @method Payment setMethodCode(string $value)
 * @method string getCreatedAt()
 * @method Payment setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Payment setUpdatedAt(string $value)
 * @method Payment setSubscriberName(string $value)
 * @method string getStartDatetime()
 * @method Payment setStartDatetime(string $value)
 * @method Payment setInternalReferenceId(string $value)
 * @method Payment setScheduleDescription(string $value)
 * @method int getSuspensionThreshold()
 * @method Payment setSuspensionThreshold(int $value)
 * @method int getBillFailedLater()
 * @method Payment setBillFailedLater(int $value)
 * @method string getPeriodUnit()
 * @method Payment setPeriodUnit(string $value)
 * @method int getPeriodFrequency()
 * @method Payment setPeriodFrequency(int $value)
 * @method int getPeriodMaxCycles()
 * @method Payment setPeriodMaxCycles(int $value)
 * @method float getBillingAmount()
 * @method Payment setBillingAmount(float $value)
 * @method string getTrialPeriodUnit()
 * @method Payment setTrialPeriodUnit(string $value)
 * @method int getTrialPeriodFrequency()
 * @method Payment setTrialPeriodFrequency(int $value)
 * @method int getTrialPeriodMaxCycles()
 * @method Payment setTrialPeriodMaxCycles(int $value)
 * @method float getTrialBillingAmount()
 * @method Payment setTrialBillingAmount(float $value)
 * @method string getCurrencyCode()
 * @method Payment setCurrencyCode(string $value)
 * @method float getShippingAmount()
 * @method Payment setShippingAmount(float $value)
 * @method float getTaxAmount()
 * @method Payment setTaxAmount(float $value)
 * @method float getInitAmount()
 * @method Payment setInitAmount(float $value)
 * @method int getInitMayFail()
 * @method Payment setInitMayFail(int $value)
 * @method string getOrderInfo()
 * @method Payment setOrderInfo(string $value)
 * @method string getOrderItemInfo()
 * @method Payment setOrderItemInfo(string $value)
 * @method string getBillingAddressInfo()
 * @method Payment setBillingAddressInfo(string $value)
 * @method string getShippingAddressInfo()
 * @method Payment setShippingAddressInfo(string $value)
 * @method string getPaymentVendorInfo()
 * @method Payment setPaymentVendorInfo(string $value)
 * @method string getAdditionalInfo()
 * @method Payment setAdditionalInfo(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payment extends \Magento\RecurringPayment\Model\RecurringPayment
{
    /**
     * Allowed actions matrix
     *
     * @var array
     */
    protected $_workflow = null;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Sales\Model\Order\PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var States
     */
    protected $states;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param PeriodUnits $periodUnits
     * @param \Magento\RecurringPayment\Block\Fields $fields
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param ManagerInterfaceFactory $managerFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\LocaleInterface $locale
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     * @param \Magento\Sales\Model\Order\PaymentFactory $paymentFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param States $states
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\RecurringPayment\Model\PeriodUnits $periodUnits,
        \Magento\RecurringPayment\Block\Fields $fields,
        ManagerInterfaceFactory $managerFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\LocaleInterface $locale,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        \Magento\Sales\Model\Order\PaymentFactory $paymentFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Math\Random $mathRandom,
        States $states,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_addressFactory = $addressFactory;
        $this->_paymentFactory = $paymentFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->mathRandom = $mathRandom;
        $this->states = $states;
        parent::__construct(
            $context,
            $registry,
            $paymentData,
            $periodUnits,
            $fields,
            $managerFactory,
            $localeDate,
            $localeResolver,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Load order by system increment identifier
     *
     * @param string $internalReferenceId
     * @return \Magento\Sales\Model\Order
     */
    public function loadByInternalReferenceId($internalReferenceId)
    {
        return $this->load($internalReferenceId, 'internal_reference_id');
    }

    /**
     * Submit a recurring payment right after an order is placed
     *
     * @return void
     * @throws \Exception
     */
    public function submit()
    {
        $this->_getResource()->beginTransaction();
        try {
            $this->setInternalReferenceId($this->mathRandom->getUniqueHash('temporary-'));
            $this->save();
            $this->setInternalReferenceId($this->mathRandom->getUniqueHash($this->getId() . '-'));
            $this->getManager()->submit($this, $this->getQuote()->getPayment());
            $this->save();
            $this->_getResource()->commit();
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
    }

    /**
     * Activate the suspended payment
     *
     * @return void
     */
    public function activate()
    {
        $this->_checkWorkflow(States::ACTIVE, false);
        $this->setNewState(States::ACTIVE);
        $this->getManager()->updateStatus($this);
        $this->setState(States::ACTIVE)->save();
    }

    /**
     * Check whether the workflow allows to activate the payment
     *
     * @return bool
     */
    public function canActivate()
    {
        return $this->_checkWorkflow(States::ACTIVE);
    }

    /**
     * Suspend active payment
     *
     * @return void
     */
    public function suspend()
    {
        $this->_checkWorkflow(States::SUSPENDED, false);
        $this->setNewState(States::SUSPENDED);
        $this->getManager()->updateStatus($this);
        $this->setState(States::SUSPENDED)->save();
    }

    /**
     * Check whether the workflow allows to suspend the payment
     *
     * @return bool
     */
    public function canSuspend()
    {
        return $this->_checkWorkflow(States::SUSPENDED);
    }

    /**
     * Cancel active or suspended payment
     *
     * @return void
     */
    public function cancel()
    {
        $this->_checkWorkflow(States::CANCELED, false);
        $this->setNewState(States::CANCELED);
        $this->getManager()->updateStatus($this);
        $this->setState(States::CANCELED)->save();
    }

    /**
     * Check whether the workflow allows to cancel the payment
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->_checkWorkflow(States::CANCELED);
    }

    /**
     * @return void
     */
    public function fetchUpdate()
    {
        $result = new \Magento\Framework\Object();
        $this->getManager()->getDetails($this->getReferenceId(), $result);

        if ($result->getIsPaymentActive()) {
            $this->setState(States::ACTIVE);
        } elseif ($result->getIsPaymentPending()) {
            $this->setState(States::PENDING);
        } elseif ($result->getIsPaymentCanceled()) {
            $this->setState(States::CANCELED);
        } elseif ($result->getIsPaymentSuspended()) {
            $this->setState(States::SUSPENDED);
        } elseif ($result->getIsPaymentExpired()) {
            $this->setState(States::EXPIRED);
        }
    }

    /**
     * @return bool
     */
    public function canFetchUpdate()
    {
        return $this->getManager()->canGetDetails();
    }

    /**
     * Initialize new order based on payment data
     *
     * Takes arbitrary number of \Magento\Framework\Object instances to be treated as items for new order
     *
     * @return \Magento\Sales\Model\Order
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function createOrder()
    {
        $items = array();
        $itemInfoObjects = func_get_args();

        $billingAmount = 0;
        $shippingAmount = 0;
        $taxAmount = 0;
        $isVirtual = 1;
        $weight = 0;
        foreach ($itemInfoObjects as $itemInfo) {
            $item = $this->_getItem($itemInfo);
            $billingAmount += $item->getPrice();
            $shippingAmount += $item->getShippingAmount();
            $taxAmount += $item->getTaxAmount();
            $weight += $item->getWeight();
            if (!$item->getIsVirtual()) {
                $isVirtual = 0;
            }
            $items[] = $item;
        }
        $grandTotal = $billingAmount + $shippingAmount + $taxAmount;

        $order = $this->_orderFactory->create();

        $billingAddress = $this->_addressFactory->create()->setData($this->getBillingAddressInfo())->setId(null);

        $shippingInfo = $this->getShippingAddressInfo();
        $shippingAddress = $this->_addressFactory->create()->setData($shippingInfo)->setId(null);

        $payment = $this->_paymentFactory->create()->setMethod($this->getMethodCode());

        $transferDataKeys = array(
            'store_id',
            'store_name',
            'customer_id',
            'customer_email',
            'customer_firstname',
            'customer_lastname',
            'customer_middlename',
            'customer_prefix',
            'customer_suffix',
            'customer_taxvat',
            'customer_gender',
            'customer_is_guest',
            'customer_note_notify',
            'customer_group_id',
            'customer_note',
            'shipping_method',
            'shipping_description',
            'base_currency_code',
            'global_currency_code',
            'order_currency_code',
            'store_currency_code',
            'base_to_global_rate',
            'base_to_order_rate',
            'store_to_base_rate',
            'store_to_order_rate'
        );

        $orderInfo = $this->getOrderInfo();
        foreach ($transferDataKeys as $key) {
            if (isset($orderInfo[$key])) {
                $order->setData($key, $orderInfo[$key]);
            } elseif (isset($shippingInfo[$key])) {
                $order->setData($key, $shippingInfo[$key]);
            }
        }

        $order->setStoreId(
            $this->getStoreId()
        )->setState(
            \Magento\Sales\Model\Order::STATE_NEW
        )->setBaseToOrderRate(
            $this->getInfoValue('order_info', 'base_to_quote_rate')
        )->setStoreToOrderRate(
            $this->getInfoValue('order_info', 'store_to_quote_rate')
        )->setOrderCurrencyCode(
            $this->getInfoValue('order_info', 'quote_currency_code')
        )->setBaseSubtotal(
            $billingAmount
        )->setSubtotal(
            $billingAmount
        )->setBaseShippingAmount(
            $shippingAmount
        )->setShippingAmount(
            $shippingAmount
        )->setBaseTaxAmount(
            $taxAmount
        )->setTaxAmount(
            $taxAmount
        )->setBaseGrandTotal(
            $grandTotal
        )->setGrandTotal(
            $grandTotal
        )->setIsVirtual(
            $isVirtual
        )->setWeight(
            $weight
        )->setTotalQtyOrdered(
            $this->getInfoValue('order_info', 'items_qty')
        )->setBillingAddress(
            $billingAddress
        )->setShippingAddress(
            $shippingAddress
        )->setPayment(
            $payment
        );

        foreach ($items as $item) {
            $order->addItem($item);
        }

        return $order;
    }

    /**
     * Validate states
     *
     * @return bool
     */
    public function isValid()
    {
        parent::isValid();

        // state
        if (!array_key_exists($this->getState(), $this->states->toOptionArray())) {
            $this->_errors['state'][] = __('Wrong state: "%1"', $this->getState());
        }

        return empty($this->_errors);
    }

    /**
     * Import quote information to the payment
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return $this
     */
    public function importQuote(\Magento\Sales\Model\Quote $quote)
    {
        $this->setQuote($quote);

        if ($quote->getPayment() && $quote->getPayment()->getMethod()) {
            $this->setManager(
                $this->_managerFactory->create(array('paymentMethod' => $quote->getPayment()->getMethodInstance()))
            );
        }

        $orderInfo = $quote->getData();
        $this->_cleanupArray($orderInfo);
        $this->setOrderInfo($orderInfo);

        $addressInfo = $quote->getBillingAddress()->getData();
        $this->_cleanupArray($addressInfo);
        $this->setBillingAddressInfo($addressInfo);
        if (!$quote->isVirtual()) {
            $addressInfo = $quote->getShippingAddress()->getData();
            $this->_cleanupArray($addressInfo);
            $this->setShippingAddressInfo($addressInfo);
        }

        $this->setCurrencyCode($quote->getBaseCurrencyCode());
        $this->setCustomerId($quote->getCustomerId());
        $this->setStoreId($quote->getStoreId());

        return $this;
    }

    /**
     * Import quote item information to the payment
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    public function importQuoteItem(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $this->setQuoteItemInfo($item);

        // TODO: make it abstract from amounts
        $this->setBillingAmount(
            $item->getBaseRowTotal()
        )->setTaxAmount(
            $item->getBaseTaxAmount()
        )->setShippingAmount(
            $item->getBaseShippingAmount()
        );
        if (!$this->getScheduleDescription()) {
            $this->setScheduleDescription($item->getName());
        }

        $orderItemInfo = $item->getData();
        $this->_cleanupArray($orderItemInfo);

        $customOptions = $item->getOptionsByCode();
        if ($customOptions['info_buyRequest']) {
            $orderItemInfo['info_buyRequest'] = $customOptions['info_buyRequest']->getValue();
        }

        $this->setOrderItemInfo($orderItemInfo);

        return $this->_filterValues();
    }

    /**
     * Render state as label
     *
     * @param string $key
     * @return array|null
     */
    public function renderData($key)
    {
        $value = $this->_getData($key);
        switch ($key) {
            case 'state':
                $states = $this->states->toOptionArray();
                return $states[$value];
        }
        return parent::renderData($key);
    }

    /**
     * Getter for additional information value
     * It is assumed that the specified additional info is an object or associative array
     *
     * @param string $infoKey
     * @param string $infoValueKey
     * @return mixed
     */
    public function getInfoValue($infoKey, $infoValueKey)
    {
        $info = $this->getData($infoKey);
        if (!$info) {
            return;
        }
        if (!is_object($info)) {
            if (is_array($info) && isset($info[$infoValueKey])) {
                return $info[$infoValueKey];
            }
        } else {
            if ($info instanceof \Magento\Framework\Object) {
                return $info->getDataUsingMethod($infoValueKey);
            } elseif (isset($info->{$infoValueKey})) {
                return $info->{$infoValueKey};
            }
        }
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\RecurringPayment\Model\Resource\Payment');
    }

    /**
     * Automatically set "unknown" state if not defined
     *
     * @return \Magento\RecurringPayment\Model\RecurringPayment
     */
    protected function _filterValues()
    {
        $result = parent::_filterValues();

        if (!$this->getState()) {
            $this->setState(States::UNKNOWN);
        }

        return $result;
    }

    /**
     * Initialize the workflow reference
     *
     * @return void
     */
    protected function _initWorkflow()
    {
        if (null === $this->_workflow) {
            $this->_workflow = array(
                'unknown' => array('pending', 'active', 'suspended', 'canceled'),
                'pending' => array('active', 'canceled'),
                'active' => array('suspended', 'canceled'),
                'suspended' => array('active', 'canceled'),
                'canceled' => array(),
                'expired' => array()
            );
        }
    }

    /**
     * Check whether payment can be changed to specified state
     *
     * @param string $againstState
     * @param bool $soft
     * @return bool
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _checkWorkflow($againstState, $soft = true)
    {
        $this->_initWorkflow();
        $state = $this->getState();
        $result = !empty($this->_workflow[$state]) && in_array($againstState, $this->_workflow[$state]);
        if (!$soft && !$result) {
            throw new \Magento\Framework\Model\Exception(
                __('This payment state cannot be changed to "%1".', $againstState)
            );
        }
        return $result;
    }

    /**
     * Return recurring payment child orders Ids
     *
     * @return string[]
     */
    public function getChildOrderIds()
    {
        $ids = $this->_getResource()->getChildOrderIds($this);
        if (empty($ids)) {
            $ids[] = '-1';
        }
        return $ids;
    }

    /**
     * Add order relation to recurring payment
     *
     * @param int $orderId
     * @return $this
     */
    public function addOrderRelation($orderId)
    {
        $this->getResource()->addOrderRelation($this->getId(), $orderId);
        return $this;
    }

    /**
     * Create and return new order item based on payment item data and $itemInfo
     *
     * @param \Magento\Framework\Object $itemInfo
     * @return \Magento\Sales\Model\Order\Item
     * @throws \Exception
     */
    protected function _getItem($itemInfo)
    {
        $paymentType = $itemInfo->getPaymentType();
        if (!$paymentType) {
            throw new \Exception("Recurring payment type is not specified.");
        }

        switch ($paymentType) {
            case PaymentTypeInterface::REGULAR:
                return $this->_getRegularItem($itemInfo);
            case PaymentTypeInterface::TRIAL:
                return $this->_getTrialItem($itemInfo);
            case PaymentTypeInterface::INITIAL:
                return $this->_getInitialItem($itemInfo);
            default:
                new \Exception("Invalid recurring payment type '{$paymentType}'.");
        }
    }

    /**
     * Create and return new order item based on payment item data and $itemInfo
     * for regular payment
     *
     * @param \Magento\Framework\Object $itemInfo
     * @return \Magento\Sales\Model\Order\Item
     */
    protected function _getRegularItem($itemInfo)
    {
        $price = $itemInfo->getPrice() ? $itemInfo->getPrice() : $this->getBillingAmount();
        $shippingAmount = $itemInfo->getShippingAmount() ? $itemInfo->getShippingAmount() : $this->getShippingAmount();
        $taxAmount = $itemInfo->getTaxAmount() ? $itemInfo->getTaxAmount() : $this->getTaxAmount();

        $item = $this->_orderItemFactory->create()->setData(
            $this->getOrderItemInfo()
        )->setQtyOrdered(
            $this->getInfoValue('order_item_info', 'qty')
        )->setBaseOriginalPrice(
            $this->getInfoValue('order_item_info', 'price')
        )->setPrice(
            $price
        )->setBasePrice(
            $price
        )->setRowTotal(
            $price
        )->setBaseRowTotal(
            $price
        )->setTaxAmount(
            $taxAmount
        )->setShippingAmount(
            $shippingAmount
        )->setId(
            null
        );
        return $item;
    }

    /**
     * Create and return new order item based on payment item data and $itemInfo
     * for trial payment
     *
     * @param \Magento\Framework\Object $itemInfo
     * @return \Magento\Sales\Model\Order\Item
     */
    protected function _getTrialItem($itemInfo)
    {
        $item = $this->_getRegularItem($itemInfo);

        $item->setName(__('Trial ') . $item->getName());

        $option = array('label' => __('Payment type'), 'value' => __('Trial period payment'));

        $this->_addAdditionalOptionToItem($item, $option);

        return $item;
    }

    /**
     * Create and return new order item based on payment item data and $itemInfo
     * for initial payment
     *
     * @param \Magento\Framework\Object $itemInfo
     * @return \Magento\Sales\Model\Order\Item
     */
    protected function _getInitialItem($itemInfo)
    {
        $price = $itemInfo->getPrice() ? $itemInfo->getPrice() : $this->getInitAmount();
        $shippingAmount = $itemInfo->getShippingAmount() ? $itemInfo->getShippingAmount() : 0;
        $taxAmount = $itemInfo->getTaxAmount() ? $itemInfo->getTaxAmount() : 0;
        $item = $this->_orderItemFactory->create()->setStoreId(
            $this->getStoreId()
        )->setProductType(
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
        )->setIsVirtual(
            1
        )->setSku(
            'initial_fee'
        )->setName(
            __('Recurring Payment Initial Fee')
        )->setDescription(
            ''
        )->setWeight(
            0
        )->setQtyOrdered(
            1
        )->setPrice(
            $price
        )->setOriginalPrice(
            $price
        )->setBasePrice(
            $price
        )->setBaseOriginalPrice(
            $price
        )->setRowTotal(
            $price
        )->setBaseRowTotal(
            $price
        )->setTaxAmount(
            $taxAmount
        )->setShippingAmount(
            $shippingAmount
        );

        $option = array('label' => __('Payment type'), 'value' => __('Initial period payment'));

        $this->_addAdditionalOptionToItem($item, $option);
        return $item;
    }

    /**
     * Add additional options suboption into item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array $option
     * @return void
     */
    protected function _addAdditionalOptionToItem($item, $option)
    {
        $options = $item->getProductOptions();
        $additionalOptions = $item->getProductOptionByCode('additional_options');
        if (is_array($additionalOptions)) {
            $additionalOptions[] = $option;
        } else {
            $additionalOptions = array($option);
        }
        $options['additional_options'] = $additionalOptions;
        $item->setProductOptions($options);
    }

    /**
     * Recursively cleanup array from objects
     *
     * @param array &$array
     * @return void
     */
    private function _cleanupArray(&$array)
    {
        if (!$array) {
            return;
        }
        foreach ($array as $key => $value) {
            if (is_object($value)) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                $this->_cleanupArray($array[$key]);
            }
        }
    }
}
