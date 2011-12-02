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
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * SalesRule Model
 *
 * @method Mage_SalesRule_Model_Resource_Rule _getResource()
 * @method Mage_SalesRule_Model_Resource_Rule getResource()
 * @method string getName()
 * @method Mage_SalesRule_Model_Rule setName(string $value)
 * @method string getDescription()
 * @method Mage_SalesRule_Model_Rule setDescription(string $value)
 * @method string getFromDate()
 * @method Mage_SalesRule_Model_Rule setFromDate(string $value)
 * @method string getToDate()
 * @method Mage_SalesRule_Model_Rule setToDate(string $value)
 * @method int getUsesPerCustomer()
 * @method Mage_SalesRule_Model_Rule setUsesPerCustomer(int $value)
 * @method string getCustomerGroupIds()
 * @method Mage_SalesRule_Model_Rule setCustomerGroupIds(string $value)
 * @method int getIsActive()
 * @method Mage_SalesRule_Model_Rule setIsActive(int $value)
 * @method string getConditionsSerialized()
 * @method Mage_SalesRule_Model_Rule setConditionsSerialized(string $value)
 * @method string getActionsSerialized()
 * @method Mage_SalesRule_Model_Rule setActionsSerialized(string $value)
 * @method int getStopRulesProcessing()
 * @method Mage_SalesRule_Model_Rule setStopRulesProcessing(int $value)
 * @method int getIsAdvanced()
 * @method Mage_SalesRule_Model_Rule setIsAdvanced(int $value)
 * @method string getProductIds()
 * @method Mage_SalesRule_Model_Rule setProductIds(string $value)
 * @method int getSortOrder()
 * @method Mage_SalesRule_Model_Rule setSortOrder(int $value)
 * @method string getSimpleAction()
 * @method Mage_SalesRule_Model_Rule setSimpleAction(string $value)
 * @method float getDiscountAmount()
 * @method Mage_SalesRule_Model_Rule setDiscountAmount(float $value)
 * @method float getDiscountQty()
 * @method Mage_SalesRule_Model_Rule setDiscountQty(float $value)
 * @method int getDiscountStep()
 * @method Mage_SalesRule_Model_Rule setDiscountStep(int $value)
 * @method int getSimpleFreeShipping()
 * @method Mage_SalesRule_Model_Rule setSimpleFreeShipping(int $value)
 * @method int getApplyToShipping()
 * @method Mage_SalesRule_Model_Rule setApplyToShipping(int $value)
 * @method int getTimesUsed()
 * @method Mage_SalesRule_Model_Rule setTimesUsed(int $value)
 * @method int getIsRss()
 * @method Mage_SalesRule_Model_Rule setIsRss(int $value)
 * @method string getWebsiteIds()
 * @method Mage_SalesRule_Model_Rule setWebsiteIds(string $value)
 * @method int getCouponType()
 * @method Mage_SalesRule_Model_Rule setCouponType(int $value)
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Rule extends Mage_Rule_Model_Rule
{
    const FREE_SHIPPING_ITEM = 1;
    const FREE_SHIPPING_ADDRESS = 2;

    const COUPON_TYPE_NO_COUPON = 1;
    const COUPON_TYPE_SPECIFIC  = 2;
    const COUPON_TYPE_AUTO      = 3;

    /**
     * Rule type actions
     */
    const TO_PERCENT_ACTION = 'to_percent';
    const BY_PERCENT_ACTION = 'by_percent';
    const TO_FIXED_ACTION   = 'to_fixed';
    const BY_FIXED_ACTION   = 'by_fixed';
    const CART_FIXED_ACTION = 'cart_fixed';
    const BUY_X_GET_Y_ACTION = 'buy_x_get_y';


    /**
     * @var Mage_SalesRule_Model_Coupon_CodegeneratorInterface
     */
    protected static $_couponCodeGenerator;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'salesrule_rule';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    protected $_labels = array();

    /**
     * Rule's primary coupon
     *
     * @var Mage_SalesRule_Model_Coupon
     */
    protected $_primaryCoupon;

    /**
     * Rule's subordinate coupons
     *
     * @var array of Mage_SalesRule_Model_Coupon
     */
    protected $_coupons;

    /**
     * Coupon types cache for lazy getter
     *
     * @var array
     */
    protected $_couponTypes;

    /**
     * Array of already validated addresses and validation results
     *
     * @var array
     */
    protected $_validatedAddresses = array();

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mage_SalesRule_Model_Resource_Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Set code generator instance for auto generated coupons
     *
     * @return Mage_SalesRule_Model_Coupon_CodegeneratorInterface
     */
    public static function getCouponCodeGenerator()
    {
        if (!self::$_couponCodeGenerator) {
            return Mage::getSingleton('Mage_SalesRule_Model_Coupon_Codegenerator', array('length' => 16));
        }
        return self::$_couponCodeGenerator;
    }

    /**
     * Set code generator instance for auto generated coupons
     *
     * @param Mage_SalesRule_Model_Coupon_CodegeneratorInterface
     */
    public static function setCouponCodeGenerator(Mage_SalesRule_Model_Coupon_CodegeneratorInterface $codeGenerator)
    {
        self::$_couponCodeGenerator = $codeGenerator;
    }

    /**
     * Retrieve rule's primary coupon
     *
     * @return Mage_SalesRule_Model_Coupon
     */
    public function getPrimaryCoupon()
    {
        if ($this->_primaryCoupon === null) {
            $this->_primaryCoupon = Mage::getModel('Mage_SalesRule_Model_Coupon');
            $this->_primaryCoupon->loadPrimaryByRule($this->getId());
            $this->_primaryCoupon->setRule($this)->setIsPrimary(true);
        }
        return $this->_primaryCoupon;
    }

    /**
     * Processing object after load data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        $this->setCouponCode($this->getPrimaryCoupon()->getCode());
        $this->setUsesPerCoupon($this->getPrimaryCoupon()->getUsageLimit());
        return parent::_afterLoad();
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('Mage_SalesRule_Model_Rule_Condition_Combine');
    }

    public function getActionsInstance()
    {
        return Mage::getModel('Mage_SalesRule_Model_Rule_Condition_Product_Combine');
    }

    public function toString($format='')
    {
        $str = Mage::helper('Mage_SalesRule_Helper_Data')->__("Name: %s", $this->getName()) ."\n"
             . Mage::helper('Mage_SalesRule_Helper_Data')->__("Start at: %s", $this->getStartAt()) ."\n"
             . Mage::helper('Mage_SalesRule_Helper_Data')->__("Expire at: %s", $this->getExpireAt()) ."\n"
             . Mage::helper('Mage_SalesRule_Helper_Data')->__("Customer registered: %s", $this->getCustomerRegistered()) ."\n"
             . Mage::helper('Mage_SalesRule_Helper_Data')->__("Customer is new buyer: %s", $this->getCustomerNewBuyer()) ."\n"
             . Mage::helper('Mage_SalesRule_Helper_Data')->__("Description: %s", $this->getDescription()) ."\n\n"
             . $this->getConditions()->toStringRecursive() ."\n\n"
             . $this->getActions()->toStringRecursive() ."\n\n";
        return $str;
    }

    /**
     * Initialize rule model data from array
     *
     * @param   array $rule
     * @return  Mage_SalesRule_Model_Rule
     */
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions(array())->loadArray($arr['actions'][1], 'actions');
        }
        if (isset($rule['store_labels'])) {
            $this->setStoreLabels($rule['store_labels']);
        }
        return $this;
    }

    /**
     * Returns rule as an array for admin interface
     *
     * Output example:
     * array(
     *   'name'=>'Example rule',
     *   'conditions'=>{condition_combine::toArray}
     *   'actions'=>{action_collection::toArray}
     * )
     *
     * @return array
     */
    public function toArray(array $arrAttributes = array())
    {
        $out = parent::toArray($arrAttributes);
        $out['customer_registered'] = $this->getCustomerRegistered();
        $out['customer_new_buyer'] = $this->getCustomerNewBuyer();

        return $out;
    }

    public function getResourceCollection()
    {
        return Mage::getResourceModel('Mage_SalesRule_Model_Resource_Rule_Collection');
    }

    /**
     * Save rule labels after rule save and process product attributes used in actions and conditions
     *
     * @return Mage_SalesRule_Model_Rule
     */
    protected function _afterSave()
    {
        if ($this->hasStoreLabels()) {
            $this->_getResource()->saveStoreLabels($this->getId(), $this->getStoreLabels());
        }
        $couponCode = trim($this->getCouponCode());
        if ($couponCode && $this->getCouponType() == self::COUPON_TYPE_SPECIFIC) {
            $this->getPrimaryCoupon()
                ->setCode($couponCode)
                ->setUsageLimit($this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null)
                ->setUsagePerCustomer($this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null)
                ->setExpirationDate($this->getToDate())
                ->save();
        } else {
            $this->getPrimaryCoupon()->delete();
        }

        //Saving attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
        if (count($ruleProductAttributes)) {
            $this->getResource()->setActualProductAttributes($this, $ruleProductAttributes);
        }
        return parent::_afterSave();
    }

    /**
     * Get Rule label for specific store
     *
     * @param   store $store
     * @return  string | false
     */
    public function getStoreLabel($store=null)
    {
        $storeId = Mage::app()->getStore($store)->getId();
        if ($this->hasStoreLabels()) {
            $labels = $this->_getData('store_labels');
            if (isset($labels[$storeId])) {
                return $labels[$storeId];
            } elseif ($labels[0]) {
                return $labels[0];
            }
            return false;
        } elseif (!isset($this->_labels[$storeId])) {
            $this->_labels[$storeId] = $this->_getResource()->getStoreLabel($this->getId(), $storeId);
        }
        return $this->_labels[$storeId];
    }

    /**
     * Get all existing rule labels
     *
     * @return array
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }
        return $this->_getData('store_labels');
    }

    /**
     * Retrieve subordinate coupons
     *
     * @return array of Mage_SalesRule_Model_Coupon
     */
    public function getCoupons()
    {
        if ($this->_coupons === null) {
            $collection = Mage::getResourceModel('Mage_SalesRule_Model_Resource_Coupon_Collection');
            /** @var Mage_SalesRule_Model_Resource_Coupon_Collection */
            $collection->addRuleToFilter($this);
            $this->_coupons = $collection->getItems();
        }
        return $this->_coupons;
    }

    /**
     * Retrieve coupon types
     *
     * @return array
     */
    public function getCouponTypes()
    {
        if ($this->_couponTypes === null) {
            $this->_couponTypes = array(
                Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON => Mage::helper('Mage_SalesRule_Helper_Data')->__('No Coupon'),
                Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC  => Mage::helper('Mage_SalesRule_Helper_Data')->__('Specific Coupon'),
            );
            $transport = new Varien_Object(array(
                    'coupon_types' => $this->_couponTypes,
                    'is_coupon_type_auto_visible' => false
            ));
            Mage::dispatchEvent('salesrule_rule_get_coupon_types', array('transport' => $transport));
            $this->_couponTypes = $transport->getCouponTypes();
            if ($transport->getIsCouponTypeAutoVisible()) {
                $this->_couponTypes[Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO] = Mage::helper('Mage_SalesRule_Helper_Data')->__('Auto');
            }
        }
        return $this->_couponTypes;
    }

    /**
     * Acquire coupon instance
     *
     * @param  bool Whether or not to save newly created coupon
     * @param  int Number of attempts to save newly created coupon
     * @return Mage_SalesRule_Model_Coupon|null
     */
    public function acquireCoupon($saveNewlyCreated = true, $saveAttemptCount = 10)
    {
        if ($this->getCouponType() == self::COUPON_TYPE_NO_COUPON) {
            return null;
        }
        if ($this->getCouponType() == self::COUPON_TYPE_SPECIFIC) {
            return $this->getPrimaryCoupon();
        }
        /** @var Mage_SalesRule_Model_Coupon $coupon */
        $coupon = Mage::getModel('Mage_SalesRule_Model_Coupon');
        $coupon->setRule($this)->setIsPrimary(false);
        $coupon->setUsageLimit($this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null)
                ->setUsagePerCustomer($this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null)
                ->setExpirationDate($this->getToDate());

        $couponCode = self::getCouponCodeGenerator()->generateCode();
        $coupon->setCode($couponCode);
        $ok = false;
        if (!$saveNewlyCreated) {
            $ok = true;
        } else if ($this->getId()) {
            for ($attemptNum = 0; $attemptNum < $saveAttemptCount; $attemptNum++) {
                try {
                    $coupon->save();
                } catch (Exception $e) {
                    if ($e instanceof Mage_Core_Exception || $coupon->getId()) {
                        throw $e;
                    }
                    $coupon->setCode(
                        $couponCode .
                        self::getCouponCodeGenerator()->getDelimiter() .
                        sprintf('%04u', rand(0, 9999))
                    );
                    continue;
                }
                $ok = true;
                break;
            }
        }
        if (!$ok) {
            Mage::throwException(Mage::helper('Mage_SalesRule_Helper_Data')->__('Can\'t acquire coupon.'));
        }

        return $coupon;
    }

    /**
     * Return all product attributes used on serialized action or condition
     *
     * @param string $serializedString
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        $result = array();
        if (preg_match_all('~s:32:"salesrule/rule_condition_product";s:9:"attribute";s:\d+:"(.*?)"~s',
            $serializedString, $matches)){
            foreach ($matches[1] as $offset => $attributeCode) {
                $result[] = $attributeCode;
            }
        }
        return $result;
    }

    /**
     * Check cached validation result for specific address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->_validatedAddresses[$addressId]) ? true : false;
    }

    /**
     * Set validation result for specific address to results cache
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   bool $validationResult
     * @return  Mage_SalesRule_Model_Rule
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->_validatedAddresses[$addressId] = $validationResult;
        return $this;
    }

    /**
     * Get cached validation result for specific address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->_validatedAddresses[$addressId]) ? $this->_validatedAddresses[$addressId] : false;
    }

    /**
     * Return id for address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  string
     */
    private function _getAddressId($address) {
        if($address instanceof Mage_Sales_Model_Quote_Address) {
            return $address->getId();
        }
        return $address;
    }
}
