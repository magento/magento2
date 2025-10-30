<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\SalesRule\Model;

use Laminas\Validator\ValidatorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as RulesCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * SalesRule Validator Model
 *
 * Allows dispatching before and after events for each controller action
 *
 * @method string getCouponCode()
 * @method Validator setCouponCode($code)
 * @method string[] getCouponCodes()
 * @method Validator setCouponCodes($codes)
 * @method int getWebsiteId()
 * @method Validator setWebsiteId($id)
 * @method int getCustomerGroupId()
 * @method Validator setCustomerGroupId($id)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validator extends \Magento\Framework\Model\AbstractModel implements ResetAfterRequestInterface
{
    /**
     * Rule source collection
     *
     * @var RulesCollection
     */
    protected $_rules;

    /**
     * Defines if method \Magento\SalesRule\Model\Validator::reset() wasn't called
     * Used for clearing applied rule ids in Quote and in Address
     *
     * @var bool
     */
    protected $_isFirstTimeResetRun = true;

    /**
     * Information about item totals for rules
     *
     * @var array
     */
    protected $_rulesItemTotals = [];

    /**
     * Skip action rules validation flag
     *
     * @var bool
     */
    protected $_skipActionsValidation = false;

    /**
     * Catalog data helper
     *
     * @var \Magento\Catalog\Helper\Data|null
     */
    protected $_catalogData = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Utility
     */
    protected $validatorUtility;

    /**
     * @var \Magento\SalesRule\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Validator\Pool
     */
    protected $validators;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Counter is used for assigning temporary id to quote address
     *
     * @var int
     */
    protected $counter = 0;

    /**
     * @var CartFixedDiscount
     */
    private $cartFixedDiscountHelper;

    /**
     * @var ValidateCouponCode
     */
    private $validateCouponCode;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Utility $utility
     * @param RulesApplier $rulesApplier
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param Validator\Pool $validators
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param CartFixedDiscount|null $cartFixedDiscount
     * @param StoreManagerInterface|null $storeManager
     * @param ValidateCouponCode|null $validateCouponCode
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\SalesRule\Model\Utility $utility,
        \Magento\SalesRule\Model\RulesApplier $rulesApplier,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\SalesRule\Model\Validator\Pool $validators,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?CartFixedDiscount $cartFixedDiscount = null,
        ?StoreManagerInterface $storeManager = null,
        ?ValidateCouponCode $validateCouponCode = null
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_catalogData = $catalogData;
        $this->validatorUtility = $utility;
        $this->rulesApplier = $rulesApplier;
        $this->priceCurrency = $priceCurrency;
        $this->validators = $validators;
        $this->messageManager = $messageManager;
        $this->cartFixedDiscountHelper = $cartFixedDiscount ?:
            ObjectManager::getInstance()->get(CartFixedDiscount::class);
        $this->storeManager = $storeManager ?:
            ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->validateCouponCode = $validateCouponCode ?:
            ObjectManager::getInstance()->get(ValidateCouponCode::class);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->counter = 0;
        $this->_skipActionsValidation = false;
        $this->_rulesItemTotals = [];
        $this->_isFirstTimeResetRun = true;
        $this->_rules = null;
    }

    /**
     * Init validator
     * Init process load collection of rules for specific website,
     * customer group and coupon code
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $couponCode
     * @return $this
     * @deprecated
     * @see self::initFromQuote()
     */
    public function init($websiteId, $customerGroupId, $couponCode)
    {
        $this->setWebsiteId($websiteId)
            ->setCustomerGroupId($customerGroupId)
            ->setCouponCode($couponCode);

        return $this;
    }

    /**
     * Init validator
     * Init process load collection of rules for specific website,
     * customer group and coupon code
     *
     * @param CartInterface $quote
     * @return $this
     * @throws NoSuchEntityException
     */
    public function initFromQuote(CartInterface $quote)
    {
        $validCoupons = $this->validateCouponCode->execute(
            $quote->getCouponCode() ? [$quote->getCouponCode()] : [],
            $quote->getCustomerIsGuest() ? null : (int) $quote->getCustomer()->getId()
        );

        if (empty($validCoupons)) {
            $quote->setCouponCode('');
        }

        $this->init(
            $this->storeManager->getStore($quote->getStoreId())->getWebsiteId(),
            $quote->getCustomerGroupId(),
            $quote->getCouponCode()
        );

        $this->setCouponCodes($validCoupons);

        return $this;
    }

    /**
     * Get rules collection for current object state
     *
     * @deprecated use getRules
     * @see we don't recommend this approach anymore
     * @param Address|null $address
     * @return RulesCollection
     * @throws \Zend_Db_Select_Exception
     */
    protected function _getRules(?Address $address = null)
    {
        return $this->getRules($address);
    }

    /**
     * Get rules collection for current object state
     *
     * @param Address|null $address
     * @return RulesCollection
     * @throws \Zend_Db_Select_Exception
     */
    public function getRules(?Address $address = null)
    {
        $addressId = $this->getAddressId($address);
        $key = $this->getCacheKey($addressId);
        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = $this->_collectionFactory->create()
                ->setValidationFilter(
                    $this->getWebsiteId(),
                    $this->getCustomerGroupId(),
                    $this->getCouponCode(),
                    null,
                    $address,
                    $this->getCouponCodes() ?? []
                )
                ->addFieldToFilter('is_active', 1)
                ->load();
        }
        return $this->_rules[$key];
    }

    /**
     * Retrieve cache key
     *
     * @param int $addressId
     * @return string
     */
    private function getCacheKey($addressId): string
    {
        $couponKey = $this->getCouponCode();
        if (!empty($this->getCouponCodes())) {
            $codes = array_values($this->getCouponCodes());
            sort($codes);
            $couponKey = implode('_', $codes);
        }
        return implode(
            '_',
            [
                $this->getWebsiteId(),
                $this->getCustomerGroupId(),
                $couponKey,
                $addressId
            ]
        );
    }

    /**
     * Address id getter.
     *
     * @param Address $address
     * @return string
     */
    protected function getAddressId(Address $address)
    {
        if ($address == null) {
            return '';
        }
        if (!$address->hasData('address_sales_rule_id')) {
            if ($address->hasData('address_id')) {
                $address->setData('address_sales_rule_id', $address->getData('address_id'));
            } else {
                $type = $address->getAddressType();
                $tempId = $type . $this->counter++;
                $address->setData('address_sales_rule_id', $tempId);
            }
        }
        return $address->getData('address_sales_rule_id');
    }

    /**
     * Set skip actions validation flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setSkipActionsValidation($flag)
    {
        $this->_skipActionsValidation = $flag;
        return $this;
    }

    /**
     * Can apply rules check
     *
     * @param AbstractItem $item
     * @return bool
     * @throws \Zend_Db_Select_Exception
     */
    public function canApplyRules(AbstractItem $item)
    {
        $address = $item->getAddress();
        foreach ($this->getRules($address) as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address) || !$rule->getActions()->validate($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reset quote and address applied rules
     *
     * @param Address $address
     * @return $this
     */
    public function reset(Address $address)
    {
        $this->validatorUtility->resetRoundingDeltas();
        $address->setBaseSubtotalWithDiscount($address->getBaseSubtotal());
        $address->setSubtotalWithDiscount($address->getSubtotal());
        $this->rulesApplier->resetDiscountAggregator();
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
     * @param AbstractItem $item
     * @param Rule $rule
     * @return $this
     * @throws \Zend_Db_Select_Exception
     */
    public function process(AbstractItem $item, Rule $rule)
    {
        $itemPrice = $this->getItemPrice($item);
        if ($itemPrice < 0) {
            return $this;
        }

        $couponCodes = [];
        if ($this->getCouponCode()) {
            $couponCodes = [$this->getCouponCode()];
        }

        if ($this->getCouponCodes()) {
            $couponCodes = $this->getCouponCodes();
        }

        $appliedRuleIds = $this->rulesApplier->applyRules(
            $item,
            [$rule],
            $this->_skipActionsValidation,
            $couponCodes
        );
        $this->rulesApplier->setAppliedRuleIds($item, $appliedRuleIds);

        return $this;
    }

    /**
     * Apply discounts to shipping amount
     *
     * @param Address $address
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Zend_Db_Select_Exception
     */
    public function processShippingAmount(Address $address)
    {
        $shippingAmount = $address->getShippingAmountForDiscount();
        if (!empty($shippingAmount)) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $shippingAmount = $address->getShippingAmount();
            $baseShippingAmount = $address->getBaseShippingAmount();
        }
        $quote = $address->getQuote();
        $appliedRuleIds = [];
        foreach ($this->getRules($address) as $rule) {
            /* @var Rule $rule */
            if (!$rule->getApplyToShipping() || !$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            $rulePercent = min(100, $rule->getDiscountAmount());
            switch ($rule->getSimpleAction()) {
                case Rule::TO_PERCENT_ACTION:
                    $rulePercent = max(0, 100 - $rule->getDiscountAmount());
                // break is intentionally omitted
                // no break
                case Rule::BY_PERCENT_ACTION:
                    $discountAmount = ($shippingAmount - $address->getShippingDiscountAmount()) * $rulePercent / 100;
                    $baseDiscountAmount = ($baseShippingAmount -
                            $address->getBaseShippingDiscountAmount()) * $rulePercent / 100;
                    $discountPercent = min(100, $address->getShippingDiscountPercent() + $rulePercent);
                    $address->setShippingDiscountPercent($discountPercent);
                    break;
                case Rule::TO_FIXED_ACTION:
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $discountAmount = $shippingAmount - $quoteAmount;
                    $baseDiscountAmount = $baseShippingAmount - $rule->getDiscountAmount();
                    break;
                case Rule::BY_FIXED_ACTION:
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $discountAmount = $quoteAmount;
                    $baseDiscountAmount = $rule->getDiscountAmount();
                    break;
                case Rule::CART_FIXED_ACTION:
                    $cartRules = $address->getCartFixedRules();
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $isAppliedToShipping = (int) $rule->getApplyToShipping();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }
                    if ($cartRules[$rule->getId()] > 0) {
                        $shippingQuoteAmount = (float) $address->getShippingAmount();
                        $quoteBaseSubtotal = (float) $quote->getBaseSubtotal();
                        $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
                        if ($isAppliedToShipping) {
                            $quoteBaseSubtotal = ($quote->getIsMultiShipping() && $isMultiShipping) ?
                                $this->cartFixedDiscountHelper->getQuoteTotalsForMultiShipping($quote) :
                                $this->cartFixedDiscountHelper->getQuoteTotalsForRegularShipping(
                                    $address,
                                    $quoteBaseSubtotal,
                                    $shippingQuoteAmount
                                );
                            $discountAmount = $this->cartFixedDiscountHelper->
                            getShippingDiscountAmount(
                                $rule,
                                $shippingQuoteAmount,
                                $quoteBaseSubtotal
                            );
                            $baseDiscountAmount = $discountAmount;
                        } else {
                            $discountAmount = min($shippingQuoteAmount, $quoteAmount);
                            $baseDiscountAmount = min(
                                $baseShippingAmount - $address->getBaseShippingDiscountAmount(),
                                $cartRules[$rule->getId()]
                            );
                        }
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);
                    break;
                case Rule::BUY_X_GET_Y_ACTION:
                    $allQtyDiscount = $this->getDiscountQtyAllItemsBuyXGetYAction($quote, $rule);
                    $quoteAmount = $address->getBaseShippingAmount() / $quote->getItemsQty() * $allQtyDiscount;
                    $discountAmount = $this->priceCurrency->convert($quoteAmount, $quote->getStore());
                    $baseDiscountAmount = $quoteAmount;
                    break;
            }
            if ($address->getShippingDiscountAmount() + $discountAmount <= $shippingAmount) {
                $data = [
                    'amount' => $discountAmount,
                    'base_amount' => $baseDiscountAmount
                ];
                $this->rulesApplier->addShippingDiscountDescription(
                    $address,
                    $rule,
                    $data,
                    !empty($this->getCouponCodes()) ? $this->getCouponCodes() : [$this->getCouponCode()]
                );
            }
            $discountAmount = min($address->getShippingDiscountAmount() + $discountAmount, $shippingAmount);
            $baseDiscountAmount = min(
                $address->getBaseShippingDiscountAmount() + $baseDiscountAmount,
                $baseShippingAmount
            );
            $address->setShippingDiscountAmount($this->priceCurrency->roundPrice($discountAmount));
            $address->setBaseShippingDiscountAmount($this->priceCurrency->roundPrice($baseDiscountAmount));
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            $this->rulesApplier->maintainAddressCouponCode($address, $rule, $this->getCouponCode());
            $this->rulesApplier->addDiscountDescription(
                $address,
                $rule,
                !empty($this->getCouponCodes()) ? $this->getCouponCodes() : [$this->getCouponCode()]
            );
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        $address->setAppliedRuleIds($this->validatorUtility->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->validatorUtility->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));
        return $this;
    }

    /**
     * Calculate quote totals for each rule and save results
     *
     * @param mixed $items
     * @param Address $address
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Zend_Db_Select_Exception
     */
    public function initTotals($items, Address $address)
    {
        if (!$items) {
            return $this;
        }

        /** @var Rule $rule */
        foreach ($this->getRules($address) as $rule) {
            if (Rule::CART_FIXED_ACTION !== $rule->getSimpleAction()
                || !$this->validatorUtility->canProcessRule($rule, $address)
            ) {
                continue;
            }
            $ruleTotalItemsPrice = 0;
            $ruleTotalBaseItemsPrice = 0;
            $ruleTotalItemsDiscountAmount = 0;
            $ruleTotalBaseItemsDiscountAmount = 0;
            $validItemsCount = 0;

            $affectedItems = [];
            /** @var Quote\Item $item */
            foreach ($items as $item) {
                if (!$this->isValidItemForRule($item, $rule)) {
                    continue;
                }
                $qty = $this->validatorUtility->getItemQty($item, $rule);
                $ruleTotalItemsPrice += $this->getItemPrice($item) * $qty;
                $ruleTotalBaseItemsPrice += $this->getItemBasePrice($item) * $qty;
                $ruleTotalItemsDiscountAmount += $item->getDiscountAmount();
                $ruleTotalBaseItemsDiscountAmount += $item->getBaseDiscountAmount();
                $validItemsCount++;
                $affectedItems[] = $item;
            }

            $this->_rulesItemTotals[$rule->getId()] = [
                'items_price' => $ruleTotalItemsPrice,
                'items_discount_amount' => $ruleTotalItemsDiscountAmount,
                'base_items_price' => $ruleTotalBaseItemsPrice,
                'base_items_discount_amount' => $ruleTotalBaseItemsDiscountAmount,
                'items_count' => $validItemsCount,
                'affected_items' => $affectedItems
            ];
        }

        return $this;
    }

    /**
     * Determine if quote item is valid for a given sales rule
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @return bool
     */
    private function isValidItemForRule(AbstractItem $item, Rule $rule)
    {
        if ($item->getParentItem() && $item->getParentItem()->getProductType() === 'configurable'
            || (($item->getHasChildren() || $item->getChildren()) && $item->isChildrenCalculated())
            || $item->getNoDiscount()
        ) {
            return false;
        }

        if (!$rule->getActions()->validate($item)) {
            return false;
        }
        if (!$this->canApplyDiscount($item)) {
            return false;
        }
        return true;
    }

    /**
     * Return discount Qty for all items at Buy_X_Get_Y_Action
     *
     * @param Quote $quote
     * @param Rule $rule
     * @return float
     */
    private function getDiscountQtyAllItemsBuyXGetYAction(Quote $quote, Rule $rule): float
    {
        $discountAllQty = 0;
        foreach ($quote->getItems() as $item) {
            $qty = $item->getQty();

            $discountStep = $rule->getDiscountStep();
            $discountAmount = $rule->getDiscountAmount();
            if (!$discountStep || $discountAmount > $discountStep) {
                continue;
            }
            $buyAndDiscountQty = $discountStep + $discountAmount;

            $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
            $freeQty = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;

            $discountQty = $fullRuleQtyPeriod * $discountAmount;
            if ($freeQty > $discountStep) {
                $discountQty += $freeQty - $discountStep;
            }

            $discountAllQty += $discountQty;
        }

        return $discountAllQty;
    }

    /**
     * Return item price
     *
     * @param AbstractItem $item
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
     * @param AbstractItem $item
     * @return float
     */
    public function getItemOriginalPrice($item)
    {
        return $this->_catalogData->getTaxPrice($item, $item->getOriginalPrice(), true);
    }

    /**
     * Return item base price
     *
     * @param AbstractItem $item
     * @return float
     */
    public function getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return $price !== null ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    /**
     * Return item base original price
     *
     * @param AbstractItem $item
     * @return float
     */
    public function getItemBaseOriginalPrice($item)
    {
        return $this->_catalogData->getTaxPrice($item, $item->getBaseOriginalPrice(), true);
    }

    /**
     * Convert address discount description array to string
     *
     * @param Address $address
     * @param string $separator
     * @return $this
     */
    public function prepareDescription($address, $separator = ', ')
    {
        $descriptionArray = $address->getDiscountDescriptionArray();
        if (!$descriptionArray && $address->getQuote()->getItemVirtualQty() > 0) {
            $descriptionArray = $address->getQuote()->getBillingAddress()->getDiscountDescriptionArray();
        }

        $description = $descriptionArray && is_array(
            $descriptionArray
        ) ? implode(
            $separator,
            array_unique($descriptionArray)
        ) : '';

        $address->setDiscountDescription($description);
        return $this;
    }

    /**
     * Return items list sorted by possibility to apply prioritized rules
     *
     * @param array $items
     * @param Address $address
     * @return array $items
     * @throws \Zend_Db_Select_Exception
     */
    public function sortItemsByPriority($items, ?Address $address = null)
    {
        $itemsSorted = [];
        /** @var $rule Rule */
        foreach ($this->getRules($address) as $rule) {
            $actions = $rule->getActions();
            foreach ($items as $itemKey => $itemValue) {
                if ($actions->validate($itemValue)) {
                    unset($items[$itemKey]);
                    $itemsSorted[] = $itemValue;
                }
            }
        }

        if (!empty($itemsSorted)) {
            $items = array_merge($itemsSorted, $items);
        }

        return $items;
    }

    /**
     * Rule total items getter.
     *
     * @param int $key
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRuleItemTotalsInfo($key)
    {
        if (empty($this->_rulesItemTotals[$key])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Item totals are not set for the rule.'));
        }

        return $this->_rulesItemTotals[$key];
    }

    /**
     * Decrease rule items count.
     *
     * @param int $key
     * @return $this
     */
    public function decrementRuleItemTotalsCount($key)
    {
        $this->_rulesItemTotals[$key]['items_count']--;

        return $this;
    }

    /**
     * Check if we can apply discount to current QuoteItem
     *
     * @param AbstractItem $item
     * @return bool
     */
    public function canApplyDiscount(AbstractItem $item)
    {
        $result = true;
        /** @var ValidatorInterface $validator */
        foreach ($this->validators->getValidators('discount') as $validator) {
            $result = $validator->isValid($item);
            if (!$result) {
                break;
            }
        }
        return $result;
    }
}
