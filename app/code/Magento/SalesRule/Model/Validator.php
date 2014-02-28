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
 * @category    Magento
 * @package     Magento_SalesRule
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\SalesRule\Model;

/**
 * SalesRule Validator Model
 *
 * Allows dispatching before and after events for each controller action
 *
 * @method mixed getCouponCode()
 * @method \Magento\SalesRule\Model\Validator setCouponCode($code)
 * @method mixed getWebsiteId()
 * @method \Magento\SalesRule\Model\Validator setWebsiteId($id)
 * @method mixed getCustomerGroupId()
 * @method \Magento\SalesRule\Model\Validator setCustomerGroupId($id)
 */
class Validator extends \Magento\Core\Model\AbstractModel
{
    /**
     * Rule source collection
     *
     * @var \Magento\SalesRule\Model\Resource\Rule\Collection
     */
    protected $_rules;

    protected $_roundingDeltas = array();

    protected $_baseRoundingDeltas = array();

    /**
     * Defines if method \Magento\SalesRule\Model\Validator::reset() wasn't called
     * Used for clearing applied rule ids in Quote and in Address
     *
     * @var bool
     */
    protected $_isFirstTimeResetRun = true;

    /**
     * Information about item totals for rules.
     * @var array
     */
    protected $_rulesItemTotals = array();

    /**
     * Skip action rules validation flag
     *
     * @var bool
     */
    protected $_skipActionsValidation = false;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * @var \Magento\SalesRule\Model\Resource\Rule\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Resource\Coupon\UsageFactory
     */
    protected $_usageFactory;
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $_couponFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * Defines if rule with stop further rules is already applied
     *
     * @var bool
     */
    protected $_stopFurtherRules = false;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\SalesRule\Model\Resource\Coupon\UsageFactory $usageFactory
     * @param \Magento\SalesRule\Model\Resource\Rule\CollectionFactory $collectionFactory
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\Rule\CustomerFactory $customerFactory
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\SalesRule\Model\Resource\Coupon\UsageFactory $usageFactory,
        \Magento\SalesRule\Model\Resource\Rule\CollectionFactory $collectionFactory,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Rule\CustomerFactory $customerFactory,
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_usageFactory = $usageFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_taxData = $taxData;
        $this->_couponFactory = $couponFactory;
        $this->_customerFactory = $customerFactory;
        $this->calculatorFactory = $calculatorFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init validator
     * Init process load collection of rules for specific website,
     * customer group and coupon code
     *
     * @param   int $websiteId
     * @param   int $customerGroupId
     * @param   string $couponCode
     * @return  \Magento\SalesRule\Model\Validator
     */
    public function init($websiteId, $customerGroupId, $couponCode)
    {
        $this->setWebsiteId($websiteId)
            ->setCustomerGroupId($customerGroupId)
            ->setCouponCode($couponCode);

        $key = $websiteId . '_' . $customerGroupId . '_' . $couponCode;
        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = $this->_collectionFactory->create()
                ->setValidationFilter($websiteId, $customerGroupId, $couponCode)
                ->load();
        }
        return $this;
    }

    /**
     * Get rules collection for current object state
     *
     * @return \Magento\SalesRule\Model\Resource\Rule\Collection
     */
    protected function _getRules()
    {
        $key = $this->getWebsiteId() . '_' . $this->getCustomerGroupId() . '_' . $this->getCouponCode();
        return $this->_rules[$key];
    }

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param   \Magento\SalesRule\Model\Rule $rule
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  bool
     */
    protected function _canProcessRule($rule, $address)
    {
        if ($rule->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $rule->getIsValidForAddress($address);
        }

        /**
         * check per coupon usage limit
         */
        if ($rule->getCouponType() != \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON) {
            $couponCode = $address->getQuote()->getCouponCode();
            if (strlen($couponCode)) {
                /** @var \Magento\SalesRule\Model\Coupon $coupon */
                $coupon = $this->_couponFactory->create();
                $coupon->load($couponCode, 'code');
                if ($coupon->getId()) {
                    // check entire usage limit
                    if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                        $rule->setIsValidForAddress($address, false);
                        return false;
                    }
                    // check per customer usage limit
                    $customerId = $address->getQuote()->getCustomerId();
                    if ($customerId && $coupon->getUsagePerCustomer()) {
                        $couponUsage = new \Magento\Object();
                        $this->_usageFactory->create()->loadByCustomerCoupon(
                            $couponUsage, $customerId, $coupon->getId()
                        );
                        if ($couponUsage->getCouponId() &&
                            $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                        ) {
                            $rule->setIsValidForAddress($address, false);
                            return false;
                        }
                    }
                }
            }
        }

        /**
         * check per rule usage limit
         */
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId     = $address->getQuote()->getCustomerId();
            $ruleCustomer   = $this->_customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    $rule->setIsValidForAddress($address, false);
                    return false;
                }
            }
        }
        $rule->afterLoad();
        /**
         * quote does not meet rule's conditions
         */
        if (!$rule->validate($address)) {
            $rule->setIsValidForAddress($address, false);
            return false;
        }
        /**
         * passed all validations, remember to be valid
         */
        $rule->setIsValidForAddress($address, true);
        return true;
    }

    /**
     * Set skip actions validation flag
     *
     * @param   boolean $flag
     * @return  \Magento\SalesRule\Model\Validator
     */
    public function setSkipActionsValidation($flag)
    {
        $this->_skipActionsValidation = $flag;
        return $this;
    }

    /**
     * Can apply rules check
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  bool
     */
    public function canApplyRules(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $address = $item->getAddress();
        foreach ($this->_getRules() as $rule) {
            if (!$this->_canProcessRule($rule, $address) || !$rule->getActions()->validate($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Quote item free shipping ability check
     * This process not affect information about applied rules, coupon code etc.
     * This information will be added during discount amounts processing
     *
     * @param   \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return  \Magento\SalesRule\Model\Validator
     */
    public function processFreeShipping(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $address = $item->getAddress();
        $item->setFreeShipping(false);

        foreach ($this->_getRules() as $rule) {
            /* @var $rule \Magento\SalesRule\Model\Rule */
            if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }

            switch ($rule->getSimpleFreeShipping()) {
                case \Magento\SalesRule\Model\Rule::FREE_SHIPPING_ITEM:
                    $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                    break;

                case \Magento\SalesRule\Model\Rule::FREE_SHIPPING_ADDRESS:
                    $address->setFreeShipping(true);
                    break;
            }
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $this;
    }

    /**
     * Reset quote and address applied rules
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return \Magento\SalesRule\Model\Validator
     */
    public function reset(\Magento\Sales\Model\Quote\Address $address)
    {
        if ($this->_isFirstTimeResetRun) {
            $address->setAppliedRuleIds('');
            $address->getQuote()->setAppliedRuleIds('');
            $this->_isFirstTimeResetRun = false;
        }

        return $this;
    }

    /**
     * Quote item discount calculation process
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return \Magento\SalesRule\Model\Validator
     */
    public function process(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);

        $itemPrice = $this->getItemPrice($item);
        if ($itemPrice < 0) {
            return $this;
        }

        $appliedRuleIds = $this->applyRules($item);
        $this->setAppliedRuleIds($item, $appliedRuleIds);

        return $this;
    }

    /**
     * Apply discounts to shipping amount
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  \Magento\SalesRule\Model\Validator
     */
    public function processShippingAmount(\Magento\Sales\Model\Quote\Address $address)
    {
        $shippingAmount     = $address->getShippingAmountForDiscount();
        if ($shippingAmount!==null) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $shippingAmount     = $address->getShippingAmount();
            $baseShippingAmount = $address->getBaseShippingAmount();
        }
        $quote              = $address->getQuote();
        $appliedRuleIds = array();
        foreach ($this->_getRules() as $rule) {
            /* @var $rule \Magento\SalesRule\Model\Rule */
            if (!$rule->getApplyToShipping() || !$this->_canProcessRule($rule, $address)) {
                continue;
            }

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            $rulePercent = min(100, $rule->getDiscountAmount());
            switch ($rule->getSimpleAction()) {
                case \Magento\SalesRule\Model\Rule::TO_PERCENT_ACTION:
                    $rulePercent = max(0, 100-$rule->getDiscountAmount());
                case \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION:
                    $discountAmount    = ($shippingAmount - $address->getShippingDiscountAmount()) * $rulePercent/100;
                    $baseDiscountAmount= ($baseShippingAmount -
                                          $address->getBaseShippingDiscountAmount()) * $rulePercent/100;
                    $discountPercent = min(100, $address->getShippingDiscountPercent()+$rulePercent);
                    $address->setShippingDiscountPercent($discountPercent);
                    break;
                case \Magento\SalesRule\Model\Rule::TO_FIXED_ACTION:
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $shippingAmount-$quoteAmount;
                    $baseDiscountAmount= $baseShippingAmount-$rule->getDiscountAmount();
                    break;
                case \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION:
                    $quoteAmount        = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount     = $quoteAmount;
                    $baseDiscountAmount = $rule->getDiscountAmount();
                    break;
                case \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION:
                    $cartRules = $address->getCartFixedRules();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }
                    if ($cartRules[$rule->getId()] > 0) {
                        $quoteAmount        = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                        $discountAmount     = min(
                            $shippingAmount-$address->getShippingDiscountAmount(),
                            $quoteAmount
                        );
                        $baseDiscountAmount = min(
                            $baseShippingAmount-$address->getBaseShippingDiscountAmount(),
                            $cartRules[$rule->getId()]
                        );
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }

                    $address->setCartFixedRules($cartRules);
                    break;
            }

            $discountAmount     = min($address->getShippingDiscountAmount()+$discountAmount, $shippingAmount);
            $baseDiscountAmount = min(
                $address->getBaseShippingDiscountAmount()+$baseDiscountAmount,
                $baseShippingAmount
            );
            $address->setShippingDiscountAmount($discountAmount);
            $address->setBaseShippingDiscountAmount($baseDiscountAmount);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            $this->_maintainAddressCouponCode($address, $rule);
            $this->_addDiscountDescription($address, $rule);
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));

        return $this;
    }

    /**
     * Merge two sets of ids
     *
     * @param array|string $a1
     * @param array|string $a2
     * @param bool $asString
     * @return array
     */
    public function mergeIds($a1, $a2, $asString = true)
    {
        if (!is_array($a1)) {
            $a1 = empty($a1) ? array() : explode(',', $a1);
        }
        if (!is_array($a2)) {
            $a2 = empty($a2) ? array() : explode(',', $a2);
        }
        $a = array_unique(array_merge($a1, $a2));
        if ($asString) {
           $a = implode(',', $a);
        }
        return $a;
    }

    /**
     * Calculate quote totals for each rule and save results
     *
     * @param mixed $items
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return \Magento\SalesRule\Model\Validator
     */
    public function initTotals($items, \Magento\Sales\Model\Quote\Address $address)
    {
        $address->setCartFixedRules(array());

        if (!$items) {
            return $this;
        }

        foreach ($this->_getRules() as $rule) {
            if (\Magento\SalesRule\Model\Rule::CART_FIXED_ACTION == $rule->getSimpleAction()
                && $this->_canProcessRule($rule, $address)) {

                $ruleTotalItemsPrice = 0;
                $ruleTotalBaseItemsPrice = 0;
                $validItemsCount = 0;

                foreach ($items as $item) {
                    //Skipping child items to avoid double calculations
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (!$rule->getActions()->validate($item)) {
                        continue;
                    }
                    $qty = $this->_getItemQty($item, $rule);
                    $ruleTotalItemsPrice += $this->getItemPrice($item) * $qty;
                    $ruleTotalBaseItemsPrice += $this->getItemBasePrice($item) * $qty;
                    $validItemsCount++;
                }

                $this->_rulesItemTotals[$rule->getId()] = array(
                    'items_price'      => $ruleTotalItemsPrice,
                    'base_items_price' => $ruleTotalBaseItemsPrice,
                    'items_count'      => $validItemsCount,
                );
            }
        }

        $this->_stopFurtherRules = false;
        return $this;
    }

    /**
     * Set coupon code to address if $rule contains validated coupon
     *
     * @param  \Magento\Sales\Model\Quote\Address $address
     * @param  \Magento\SalesRule\Model\Rule $rule
     *
     * @return \Magento\SalesRule\Model\Validator
     */
    protected function _maintainAddressCouponCode($address, $rule)
    {
        /*
        Rule is a part of rules collection, which includes only rules with 'No Coupon' type or with validated coupon.
        As a result, if rule uses coupon code(s) ('Specific' or 'Auto' Coupon Type), it always contains validated coupon
        */
        if ($rule->getCouponType() != \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON) {
            $address->setCouponCode($this->getCouponCode());
        }

        return $this;
    }

    /**
     * Add rule discount description label to address object
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   \Magento\SalesRule\Model\Rule $rule
     * @return  \Magento\SalesRule\Model\Validator
     */
    protected function _addDiscountDescription($address, $rule)
    {
        $description = $address->getDiscountDescriptionArray();
        $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
        $label = '';
        if ($ruleLabel) {
            $label = $ruleLabel;
        } else if (strlen($address->getCouponCode())) {
            $label = $address->getCouponCode();
        }

        if (strlen($label)) {
            $description[$rule->getId()] = $label;
        }

        $address->setDiscountDescriptionArray($description);

        return $this;
    }

    /**
     * Return item price
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        $calcPrice = $item->getCalculationPrice();
        return $price === null ? $calcPrice : $price;
    }

    /**
     * Return item original price
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemOriginalPrice($item)
    {
        return $this->_taxData->getPrice($item, $item->getOriginalPrice(), true);
    }

    /**
     * Return item base price
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    /**
     * Return item base original price
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemBaseOriginalPrice($item)
    {
        return $this->_taxData->getPrice($item, $item->getBaseOriginalPrice(), true);
    }

    /**
     * Return discount item qty
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return int
     */
    protected function _getItemQty($item, $rule)
    {
        $qty = $item->getTotalQty();
        $discountQty = $rule->getDiscountQty();
        return $discountQty ? min($qty, $discountQty) : $qty;
    }

    /**
     * Convert address discount description array to string
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @param string $separator
     * @return \Magento\SalesRule\Model\Validator
     */
    public function prepareDescription($address, $separator=', ')
    {
        $descriptionArray = $address->getDiscountDescriptionArray();
        if (!$descriptionArray && $address->getQuote()->getItemVirtualQty() > 0) {
            $descriptionArray = $address->getQuote()->getBillingAddress()->getDiscountDescriptionArray();
        }

        $description = $descriptionArray && is_array($descriptionArray)
            ? implode($separator, array_unique($descriptionArray))
            :  '';

        $address->setDiscountDescription($description);
        return $this;
    }

    /**
     * Return items list sorted by possibility to apply prioritized rules
     *
     * @param array $items
     * @return array $items
     */
    public function sortItemsByPriority($items)
    {
        $itemsSorted = array();
        /** @var $rule \Magento\SalesRule\Model\Rule */
        foreach ($this->_getRules() as $rule) {
            foreach ($items as $itemKey => $itemValue) {
                if ($rule->getActions()->validate($itemValue)) {
                    unset($items[$itemKey]);
                    array_push($itemsSorted, $itemValue);
                }
            }
        }

        if (!empty($itemsSorted)) {
            $items = array_merge($itemsSorted, $items);
        }

        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param int[] $appliedRuleIds
     * @return $this
     */
    protected function setAppliedRuleIds(\Magento\Sales\Model\Quote\Item\AbstractItem $item, array $appliedRuleIds)
    {
        $address = $item->getAddress();
        $quote = $item->getQuote();

        $item->setAppliedRuleIds(join(',', $appliedRuleIds));
        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));

        return $this;
    }

    /**
     * Fire event to allow overwriting of discount amounts
     *
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param float $qty
     * @return $this
     */
    protected function eventFix(
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        \Magento\Sales\Model\Quote\Item\AbstractItem $item,
        \Magento\SalesRule\Model\Rule $rule,
        $qty
    ) {
        $quote = $item->getQuote();
        $address = $item->getAddress();

        $this->_eventManager->dispatch('salesrule_validator_process', array(
            'rule'    => $rule,
            'item'    => $item,
            'address' => $address,
            'quote'   => $quote,
            'qty'     => $qty,
            'result'  => $discountData,
        ));

        return $this;
    }

    /**
     * Process "delta" rounding
     *
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    protected function deltaRoundingFix(
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        \Magento\Sales\Model\Quote\Item\AbstractItem $item
    ) {
        $store = $item->getQuote()->getStore();
        $discountAmount = $discountData->getAmount();
        $baseDiscountAmount = $discountData->getBaseAmount();

        //TODO Seems \Magento\Sales\Model\Quote\Item\AbstractItem::getDiscountPercent() returns float value
        //that can not be used as array index
        $percentKey = $item->getDiscountPercent();
        if ($percentKey) {
            $delta      = isset($this->_roundingDeltas[$percentKey]) ? $this->_roundingDeltas[$percentKey] : 0;
            $baseDelta  = isset($this->_baseRoundingDeltas[$percentKey]) ? $this->_baseRoundingDeltas[$percentKey] : 0;

            $discountAmount += $delta;
            $baseDiscountAmount += $baseDelta;

            $this->_roundingDeltas[$percentKey] = $discountAmount - $store->roundPrice($discountAmount);
            $this->_baseRoundingDeltas[$percentKey] = $baseDiscountAmount - $store->roundPrice($baseDiscountAmount);
        }

        $discountData->setAmount($store->roundPrice($discountAmount));
        $discountData->setBaseAmount($store->roundPrice($baseDiscountAmount));

        return $this;
    }

    /**
     * @param Rule\Action\Discount\Data $discountData
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     */
    protected function minFix(
        \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData,
        \Magento\Sales\Model\Quote\Item\AbstractItem $item,
        $qty
    ) {
        $itemPrice = $this->getItemPrice($item);
        $baseItemPrice = $this->getItemBasePrice($item);

        $itemDiscountAmount = $item->getDiscountAmount();
        $itemBaseDiscountAmount = $item->getBaseDiscountAmount();

        $discountAmount = min($itemDiscountAmount + $discountData->getAmount(), $itemPrice * $qty);
        $baseDiscountAmount = min($itemBaseDiscountAmount + $discountData->getBaseAmount(), $baseItemPrice * $qty);

        $discountData->setAmount($discountAmount);
        $discountData->setBaseAmount($baseDiscountAmount);
    }

    /**
     * @param int $key
     * @return array
     * @throws \Magento\Core\Exception
     */
    public function getRuleItemTotalsInfo($key)
    {
        if (empty($this->_rulesItemTotals[$key])) {
            throw new \Magento\Core\Exception(__('Item totals are not set for the rule.'));
        }

        return $this->_rulesItemTotals[$key];
    }

    /**
     * @param int $key
     * @return $this
     */
    public function decrementRuleItemTotalsCount($key)
    {
        $this->_rulesItemTotals[$key]['items_count']--;

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return array
     */
    protected function applyRules($item)
    {
        $address = $item->getAddress();

        $appliedRuleIds = array();
        /* @var $rule \Magento\SalesRule\Model\Rule */
        foreach ($this->_getRules() as $rule) {
            if ($this->_stopFurtherRules) {
                break;
            }

            if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }

            if (!$this->_skipActionsValidation && !$rule->getActions()->validate($item)) {
                continue;
            }

            $this->applyRule($item, $rule, $address);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            if ($rule->getStopRulesProcessing()) {
                $this->_stopFurtherRules = true;
                break;
            }
        }

        return $appliedRuleIds;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    protected function applyRule($item, $rule, $address)
    {
        $discountData = $this->getDiscountData($item, $rule);
        $this->setDiscountData($discountData, $item);

        $this->_maintainAddressCouponCode($address, $rule);
        $this->_addDiscountDescription($address, $rule);

        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return Rule\Action\Discount\Data
     */
    protected function getDiscountData($item, $rule)
    {
        $qty = $this->_getItemQty($item, $rule);

        $discountCalculator = $this->calculatorFactory->create($rule->getSimpleAction());
        $qty = $discountCalculator->fixQuantity($qty, $rule);
        $discountData = $discountCalculator->calculate($rule, $item, $qty);

        $this->eventFix($discountData, $item, $rule, $qty);
        $this->deltaRoundingFix($discountData, $item);

        /**
         * We can't use row total here because row total not include tax
         * Discount can be applied on price included tax
         */

        $this->minFix($discountData, $item, $qty);

        return $discountData;
    }

    /**
     * @param Rule\Action\Discount\Data $discountData
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    protected function setDiscountData($discountData, $item)
    {
        $item->setDiscountAmount($discountData->getAmount());
        $item->setBaseDiscountAmount($discountData->getBaseAmount());
        $item->setOriginalDiscountAmount($discountData->getOriginalAmount());
        $item->setBaseOriginalDiscountAmount($discountData->getBaseOriginalAmount());

        return $this;
    }
}
