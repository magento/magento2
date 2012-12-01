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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales implementation of recurring payment profiles
 * Implements saving and manageing profiles
 *
 * @method Mage_Sales_Model_Resource_Recurring_Profile _getResource()
 * @method Mage_Sales_Model_Resource_Recurring_Profile getResource()
 * @method string getState()
 * @method Mage_Sales_Model_Recurring_Profile setState(string $value)
 * @method int getCustomerId()
 * @method Mage_Sales_Model_Recurring_Profile setCustomerId(int $value)
 * @method int getStoreId()
 * @method Mage_Sales_Model_Recurring_Profile setStoreId(int $value)
 * @method string getMethodCode()
 * @method Mage_Sales_Model_Recurring_Profile setMethodCode(string $value)
 * @method string getCreatedAt()
 * @method Mage_Sales_Model_Recurring_Profile setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Sales_Model_Recurring_Profile setUpdatedAt(string $value)
 * @method string getReferenceId()
 * @method Mage_Sales_Model_Recurring_Profile setReferenceId(string $value)
 * @method string getSubscriberName()
 * @method Mage_Sales_Model_Recurring_Profile setSubscriberName(string $value)
 * @method string getStartDatetime()
 * @method Mage_Sales_Model_Recurring_Profile setStartDatetime(string $value)
 * @method string getInternalReferenceId()
 * @method Mage_Sales_Model_Recurring_Profile setInternalReferenceId(string $value)
 * @method string getScheduleDescription()
 * @method Mage_Sales_Model_Recurring_Profile setScheduleDescription(string $value)
 * @method int getSuspensionThreshold()
 * @method Mage_Sales_Model_Recurring_Profile setSuspensionThreshold(int $value)
 * @method int getBillFailedLater()
 * @method Mage_Sales_Model_Recurring_Profile setBillFailedLater(int $value)
 * @method string getPeriodUnit()
 * @method Mage_Sales_Model_Recurring_Profile setPeriodUnit(string $value)
 * @method int getPeriodFrequency()
 * @method Mage_Sales_Model_Recurring_Profile setPeriodFrequency(int $value)
 * @method int getPeriodMaxCycles()
 * @method Mage_Sales_Model_Recurring_Profile setPeriodMaxCycles(int $value)
 * @method float getBillingAmount()
 * @method Mage_Sales_Model_Recurring_Profile setBillingAmount(float $value)
 * @method string getTrialPeriodUnit()
 * @method Mage_Sales_Model_Recurring_Profile setTrialPeriodUnit(string $value)
 * @method int getTrialPeriodFrequency()
 * @method Mage_Sales_Model_Recurring_Profile setTrialPeriodFrequency(int $value)
 * @method int getTrialPeriodMaxCycles()
 * @method Mage_Sales_Model_Recurring_Profile setTrialPeriodMaxCycles(int $value)
 * @method float getTrialBillingAmount()
 * @method Mage_Sales_Model_Recurring_Profile setTrialBillingAmount(float $value)
 * @method string getCurrencyCode()
 * @method Mage_Sales_Model_Recurring_Profile setCurrencyCode(string $value)
 * @method float getShippingAmount()
 * @method Mage_Sales_Model_Recurring_Profile setShippingAmount(float $value)
 * @method float getTaxAmount()
 * @method Mage_Sales_Model_Recurring_Profile setTaxAmount(float $value)
 * @method float getInitAmount()
 * @method Mage_Sales_Model_Recurring_Profile setInitAmount(float $value)
 * @method int getInitMayFail()
 * @method Mage_Sales_Model_Recurring_Profile setInitMayFail(int $value)
 * @method string getOrderInfo()
 * @method Mage_Sales_Model_Recurring_Profile setOrderInfo(string $value)
 * @method string getOrderItemInfo()
 * @method Mage_Sales_Model_Recurring_Profile setOrderItemInfo(string $value)
 * @method string getBillingAddressInfo()
 * @method Mage_Sales_Model_Recurring_Profile setBillingAddressInfo(string $value)
 * @method string getShippingAddressInfo()
 * @method Mage_Sales_Model_Recurring_Profile setShippingAddressInfo(string $value)
 * @method string getProfileVendorInfo()
 * @method Mage_Sales_Model_Recurring_Profile setProfileVendorInfo(string $value)
 * @method string getAdditionalInfo()
 * @method Mage_Sales_Model_Recurring_Profile setAdditionalInfo(string $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Recurring_Profile extends Mage_Payment_Model_Recurring_Profile
{
    /**
     * Available states
     *
     * @var string
     */
    const STATE_UNKNOWN   = 'unknown';
    const STATE_PENDING   = 'pending';
    const STATE_ACTIVE    = 'active';
    const STATE_SUSPENDED = 'suspended';
    const STATE_CANCELED  = 'canceled';
    const STATE_EXPIRED   = 'expired';

    /**
     * Payment types
     *
     * @var string
     */
    const PAYMENT_TYPE_REGULAR   = 'regular';
    const PAYMENT_TYPE_TRIAL     = 'trial';
    const PAYMENT_TYPE_INITIAL   = 'initial';

    /**
     * Allowed actions matrix
     *
     * @var array
     */
    protected $_workflow = null;

    /**
     * Load order by system increment identifier
     *
     * @param string $incrementId
     * @return Mage_Sales_Model_Order
     */
    public function loadByInternalReferenceId($internalReferenceId)
    {
        return $this->load($internalReferenceId, 'internal_reference_id');
    }

    /**
     * Submit a recurring profile right after an order is placed
     *
     */
    public function submit()
    {
        $this->_getResource()->beginTransaction();
        try {
            $this->setInternalReferenceId(Mage::helper('Mage_Core_Helper_Data')->uniqHash('temporary-'));
            $this->save();
            $this->setInternalReferenceId(Mage::helper('Mage_Core_Helper_Data')->uniqHash($this->getId() . '-'));
            $this->getMethodInstance()->submitRecurringProfile($this, $this->getQuote()->getPayment());
            $this->save();
            $this->_getResource()->commit();
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
    }

    /**
     * Activate the suspended profile
     */
    public function activate()
    {
        $this->_checkWorkflow(self::STATE_ACTIVE, false);
        $this->setNewState(self::STATE_ACTIVE);
        $this->getMethodInstance()->updateRecurringProfileStatus($this);
        $this->setState(self::STATE_ACTIVE)
            ->save();
    }

    /**
     * Check whether the workflow allows to activate the profile
     *
     * @return bool
     */
    public function canActivate()
    {
        return $this->_checkWorkflow(self::STATE_ACTIVE);
    }

    /**
     * Suspend active profile
     */
    public function suspend()
    {
        $this->_checkWorkflow(self::STATE_SUSPENDED, false);
        $this->setNewState(self::STATE_SUSPENDED);
        $this->getMethodInstance()->updateRecurringProfileStatus($this);
        $this->setState(self::STATE_SUSPENDED)
            ->save();
    }

    /**
     * Check whether the workflow allows to suspend the profile
     *
     * @return bool
     */
    public function canSuspend()
    {
        return $this->_checkWorkflow(self::STATE_SUSPENDED);
    }

    /**
     * Cancel active or suspended profile
     */
    public function cancel()
    {
        $this->_checkWorkflow(self::STATE_CANCELED, false);
        $this->setNewState(self::STATE_CANCELED);
        $this->getMethodInstance()->updateRecurringProfileStatus($this);
        $this->setState(self::STATE_CANCELED)
            ->save();
    }

    /**
     * Check whether the workflow allows to cancel the profile
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->_checkWorkflow(self::STATE_CANCELED);
    }

    public function fetchUpdate()
    {
        $result = new Varien_Object();
        $this->getMethodInstance()->getRecurringProfileDetails($this->getReferenceId(), $result);

        if ($result->getIsProfileActive()) {
            $this->setState(self::STATE_ACTIVE);
        } elseif ($result->getIsProfilePending()) {
            $this->setState(self::STATE_PENDING);
        } elseif ($result->getIsProfileCanceled()) {
            $this->setState(self::STATE_CANCELED);
        } elseif ($result->getIsProfileSuspended()) {
            $this->setState(self::STATE_SUSPENDED);
        } elseif ($result->getIsProfileExpired()) {
            $this->setState(self::STATE_EXPIRED);
        }
    }

    public function canFetchUpdate()
    {
        return $this->getMethodInstance()->canGetRecurringProfileDetails();
    }

    /**
     * Initialize new order based on profile data
     *
     * Takes arbitrary number of Varien_Object instances to be treated as items for new order
     *
     * @return Mage_Sales_Model_Order
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

        $order = Mage::getModel('Mage_Sales_Model_Order');

        $billingAddress = Mage::getModel('Mage_Sales_Model_Order_Address')
            ->setData($this->getBillingAddressInfo())
            ->setId(null);

        $shippingInfo = $this->getShippingAddressInfo();
        $shippingAddress = Mage::getModel('Mage_Sales_Model_Order_Address')
            ->setData($shippingInfo)
            ->setId(null);

        $payment = Mage::getModel('Mage_Sales_Model_Order_Payment')
            ->setMethod($this->getMethodCode());

        $transferDataKays = array(
            'store_id',             'store_name',           'customer_id',          'customer_email',
            'customer_firstname',   'customer_lastname',    'customer_middlename',  'customer_prefix',
            'customer_suffix',      'customer_taxvat',      'customer_gender',      'customer_is_guest',
            'customer_note_notify', 'customer_group_id',    'customer_note',        'shipping_method',
            'shipping_description', 'base_currency_code',   'global_currency_code', 'order_currency_code',
            'store_currency_code',  'base_to_global_rate',  'base_to_order_rate',   'store_to_base_rate',
            'store_to_order_rate'
        );

        $orderInfo = $this->getOrderInfo();
        foreach ($transferDataKays as $key) {
            if (isset($orderInfo[$key])) {
                $order->setData($key, $orderInfo[$key]);
            } elseif (isset($shippingInfo[$key])) {
                $order->setData($key, $shippingInfo[$key]);
            }
        }

        $order->setStoreId($this->getStoreId())
            ->setState(Mage_Sales_Model_Order::STATE_NEW)
            ->setBaseToOrderRate($this->getInfoValue('order_info', 'base_to_quote_rate'))
            ->setStoreToOrderRate($this->getInfoValue('order_info', 'store_to_quote_rate'))
            ->setOrderCurrencyCode($this->getInfoValue('order_info', 'quote_currency_code'))
            ->setBaseSubtotal($billingAmount)
            ->setSubtotal($billingAmount)
            ->setBaseShippingAmount($shippingAmount)
            ->setShippingAmount($shippingAmount)
            ->setBaseTaxAmount($taxAmount)
            ->setTaxAmount($taxAmount)
            ->setBaseGrandTotal($grandTotal)
            ->setGrandTotal($grandTotal)
            ->setIsVirtual($isVirtual)
            ->setWeight($weight)
            ->setTotalQtyOrdered($this->getInfoValue('order_info', 'items_qty'))
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setPayment($payment);

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
        if (!in_array($this->getState(), $this->getAllStates(false), true)) {
            $this->_errors['state'][] = Mage::helper('Mage_Sales_Helper_Data')->__('Wrong state: "%s".', $this->getState());
        }

        return empty($this->_errors);
    }

    /**
     * Import quote information to the profile
     *
     * @param Mage_Sales_Model_Quote_ $quote
     * @return Mage_Sales_Model_Recurring_Profile
     */
    public function importQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->setQuote($quote);

        if ($quote->getPayment() && $quote->getPayment()->getMethod()) {
            $this->setMethodInstance($quote->getPayment()->getMethodInstance());
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
     * Import quote item information to the profile
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return Mage_Sales_Model_Recurring_Profile
     */
    public function importQuoteItem(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $this->setQuoteItemInfo($item);

        // TODO: make it abstract from amounts
        $this->setBillingAmount($item->getBaseRowTotal())
            ->setTaxAmount($item->getBaseTaxAmount())
            ->setShippingAmount($item->getBaseShippingAmount())
        ;
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
     * Getter for sales-related field labels
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldLabel($field)
    {
        switch ($field) {
            case 'order_item_id':
                return Mage::helper('Mage_Sales_Helper_Data')->__('Purchased Item');
            case 'state':
                return Mage::helper('Mage_Sales_Helper_Data')->__('Profile State');
            case 'created_at':
                return Mage::helper('Mage_Adminhtml_Helper_Data')->__('Created At');
            case 'updated_at':
                return Mage::helper('Mage_Adminhtml_Helper_Data')->__('Updated At');
            default:
                return parent::getFieldLabel($field);
        }
    }

    /**
     * Getter for sales-related field comments
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldComment($field)
    {
        switch ($field) {
            case 'order_item_id':
                return Mage::helper('Mage_Sales_Helper_Data')->__('Original order item that recurring payment profile correspondss to.');
            default:
                return parent::getFieldComment($field);
        }
    }

    /**
     * Getter for all available states
     *
     * @param bool $withLabels
     * @return array
     */
    public function getAllStates($withLabels = true)
    {
        $states = array(self::STATE_UNKNOWN, self::STATE_PENDING, self::STATE_ACTIVE,
            self::STATE_SUSPENDED, self::STATE_CANCELED, self::STATE_EXPIRED,
        );
        if ($withLabels) {
            $result = array();
            foreach ($states as $state) {
                $result[$state] = $this->getStateLabel($state);
            }
            return $result;
        }
        return $states;
    }

    /**
     * Get state label based on the code
     *
     * @param string $state
     * @return string
     */
    public function getStateLabel($state)
    {
        switch ($state) {
            case self::STATE_UNKNOWN:   return Mage::helper('Mage_Sales_Helper_Data')->__('Not Initialized');
            case self::STATE_PENDING:   return Mage::helper('Mage_Sales_Helper_Data')->__('Pending');
            case self::STATE_ACTIVE:    return Mage::helper('Mage_Sales_Helper_Data')->__('Active');
            case self::STATE_SUSPENDED: return Mage::helper('Mage_Sales_Helper_Data')->__('Suspended');
            case self::STATE_CANCELED:  return Mage::helper('Mage_Sales_Helper_Data')->__('Canceled');
            case self::STATE_EXPIRED:   return Mage::helper('Mage_Sales_Helper_Data')->__('Expired');
            default: return $state;
        }
    }

    /**
     * Render state as label
     *
     * @param string $key
     * @return mixed
     */
    public function renderData($key)
    {
        $value = $this->_getData($key);
        switch ($key) {
            case 'state':
                return $this->getStateLabel($value);
        }
        return parent::renderData($key);
    }

    /**
     * Getter for additional information value
     * It is assumed that the specified additional info is an object or associative array
     *
     * @param string $infoKey
     * @param string $infoValueKey
     * @return mixed|null
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
            if ($info instanceof Varien_Object) {
                return $info->getDataUsingMethod($infoValueKey);
            } elseif (isset($info->$infoValueKey)) {
                return $info->$infoValueKey;
            }
        }
    }

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Recurring_Profile');
    }

    /**
     * Automatically set "unknown" state if not defined
     *
     * @return Mage_Payment_Model_Recurring_Profile
     */
    protected function _filterValues()
    {
        $result = parent::_filterValues();

        if (!$this->getState()) {
            $this->setState(self::STATE_UNKNOWN);
        }

        return $result;
    }

    /**
     * Initialize the workflow reference
     */
    protected function _initWorkflow()
    {
        if (null === $this->_workflow) {
            $this->_workflow = array(
                'unknown'   => array('pending', 'active', 'suspended', 'canceled'),
                'pending'   => array('active', 'canceled'),
                'active'    => array('suspended', 'canceled'),
                'suspended' => array('active', 'canceled'),
                'canceled'  => array(),
                'expired'   => array(),
            );
        }
    }

    /**
     * Check whether profile can be changed to specified state
     *
     * @param string $againstState
     * @param bool $soft
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function _checkWorkflow($againstState, $soft = true)
    {
        $this->_initWorkflow();
        $state = $this->getState();
        $result = (!empty($this->_workflow[$state])) && in_array($againstState, $this->_workflow[$state]);
        if (!$soft && !$result) {
            Mage::throwException(
                Mage::helper('Mage_Sales_Helper_Data')->__('This profile state cannot be changed to "%s".', $againstState)
            );
        }
        return $result;
    }

    /**
     * Return recurring profile child orders Ids
     *
     * @return array
     */
    public function getChildOrderIds()
    {
        $ids = $this->_getResource()->getChildOrderIds($this);
        if (empty($ids)){
            $ids[] = '-1';
        }
        return $ids;
    }

    /**
     * Add order relation to recurring profile
     *
     * @param int $recurringProfileId
     * @return Mage_Sales_Model_Recurring_Profile
     */
    public function addOrderRelation($orderId)
    {
        $this->getResource()->addOrderRelation($this->getId(), $orderId);
        return $this;
    }

    /**
     * Create and return new order item based on profile item data and $itemInfo
     *
     * @param Varien_Object $itemInfo
     * @return Mage_Sales_Model_Order_Item
     */
    protected function _getItem($itemInfo)
    {
        $paymentType = $itemInfo->getPaymentType();
        if (!$paymentType) {
            throw new Exception("Recurring profile payment type is not specified.");
        }

        switch ($paymentType) {
            case self::PAYMENT_TYPE_REGULAR: return $this->_getRegularItem($itemInfo);
            case self::PAYMENT_TYPE_TRIAL: return $this->_getTrialItem($itemInfo);
            case self::PAYMENT_TYPE_INITIAL: return $this->_getInitialItem($itemInfo);
            default: new Exception("Invalid recurring profile payment type '{$paymentType}'.");
        }
    }

    /**
     * Create and return new order item based on profile item data and $itemInfo
     * for regular payment
     *
     * @param Varien_Object $itemInfo
     * @return Mage_Sales_Model_Order_Item
     */
    protected function _getRegularItem($itemInfo)
    {
        $price = $itemInfo->getPrice() ? $itemInfo->getPrice() : $this->getBillingAmount();
        $shippingAmount = $itemInfo->getShippingAmount() ? $itemInfo->getShippingAmount() : $this->getShippingAmount();
        $taxAmount = $itemInfo->getTaxAmount() ? $itemInfo->getTaxAmount() : $this->getTaxAmount();

        $item = Mage::getModel('Mage_Sales_Model_Order_Item')
            ->setData($this->getOrderItemInfo())
            ->setQtyOrdered($this->getInfoValue('order_item_info', 'qty'))
            ->setBaseOriginalPrice($this->getInfoValue('order_item_info', 'price'))
            ->setPrice($price)
            ->setBasePrice($price)
            ->setRowTotal($price)
            ->setBaseRowTotal($price)
            ->setTaxAmount($taxAmount)
            ->setShippingAmount($shippingAmount)
            ->setId(null);
        return $item;
    }

    /**
     * Create and return new order item based on profile item data and $itemInfo
     * for trial payment
     *
     * @param Varien_Object $itemInfo
     * @return Mage_Sales_Model_Order_Item
     */
    protected function _getTrialItem($itemInfo)
    {
        $item = $this->_getRegularItem($itemInfo);

        $item->setName(
            Mage::helper('Mage_Sales_Helper_Data')->__('Trial ') . $item->getName()
        );

        $option = array(
            'label' => Mage::helper('Mage_Sales_Helper_Data')->__('Payment type'),
            'value' => Mage::helper('Mage_Sales_Helper_Data')->__('Trial period payment')
        );

        $this->_addAdditionalOptionToItem($item, $option);

        return $item;
    }

    /**
     * Create and return new order item based on profile item data and $itemInfo
     * for initial payment
     *
     * @param Varien_Object $itemInfo
     * @return Mage_Sales_Model_Order_Item
     */
    protected function _getInitialItem($itemInfo)
    {
        $price = $itemInfo->getPrice() ? $itemInfo->getPrice() : $this->getInitAmount();
        $shippingAmount = $itemInfo->getShippingAmount() ? $itemInfo->getShippingAmount() : 0;
        $taxAmount = $itemInfo->getTaxAmount() ? $itemInfo->getTaxAmount() : 0;
        $item = Mage::getModel('Mage_Sales_Model_Order_Item')
            ->setStoreId($this->getStoreId())
            ->setProductType(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL)
            ->setIsVirtual(1)
            ->setSku('initial_fee')
            ->setName(Mage::helper('Mage_Sales_Helper_Data')->__('Recurring Profile Initial Fee'))
            ->setDescription('')
            ->setWeight(0)
            ->setQtyOrdered(1)
            ->setPrice($price)
            ->setOriginalPrice($price)
            ->setBasePrice($price)
            ->setBaseOriginalPrice($price)
            ->setRowTotal($price)
            ->setBaseRowTotal($price)
            ->setTaxAmount($taxAmount)
            ->setShippingAmount($shippingAmount);

        $option = array(
            'label' => Mage::helper('Mage_Sales_Helper_Data')->__('Payment type'),
            'value' => Mage::helper('Mage_Sales_Helper_Data')->__('Initial period payment')
        );

        $this->_addAdditionalOptionToItem($item, $option);
        return $item;
    }

    /**
     * Add additional options suboption into itev
     *
     * @param Mage_Sales_Model_Order_Item $itemInfo
     * @param array $option
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
