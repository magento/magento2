<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model;

use Exception;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\AbstractModel;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\Coupon\CodegeneratorFactory;
use Magento\SalesRule\Model\Coupon\CodegeneratorInterface as CouponCodegeneratorInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection as CouponCollection;
use Magento\SalesRule\Model\ResourceModel\Rule as ResourceRule;
use Magento\SalesRule\Model\Rule\Condition\Combine as CondCombine;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as CondProductCombine;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory as CondProductCombineFactory;
use Magento\SalesRule\Model\Rule\Condition\CombineFactory as CondCombineFactory;

/**
 * Shopping Cart Rule data model
 *
 * @api
 * @method string getName()
 * @method Rule setName(string $value)
 * @method string getDescription()
 * @method Rule setDescription(string $value)
 * @method Rule setFromDate(string $value)
 * @method Rule setToDate(string $value)
 * @method int getUsesPerCustomer()
 * @method Rule setUsesPerCustomer(int $value)
 * @method int getUsesPerCoupon()
 * @method Rule setUsesPerCoupon(int $value)
 * @method Rule setCustomerGroupIds(string $value)
 * @method int getIsActive()
 * @method Rule setIsActive(int $value)
 * @method string getConditionsSerialized()
 * @method Rule setConditionsSerialized(string $value)
 * @method string getActionsSerialized()
 * @method Rule setActionsSerialized(string $value)
 * @method int getStopRulesProcessing()
 * @method Rule setStopRulesProcessing(int $value)
 * @method int getIsAdvanced()
 * @method Rule setIsAdvanced(int $value)
 * @method string getProductIds()
 * @method Rule setProductIds(string $value)
 * @method int getSortOrder()
 * @method Rule setSortOrder(int $value)
 * @method string getSimpleAction()
 * @method Rule setSimpleAction(string $value)
 * @method float getDiscountAmount()
 * @method Rule setDiscountAmount(float $value)
 * @method float getDiscountQty()
 * @method Rule setDiscountQty(float $value)
 * @method int getDiscountStep()
 * @method Rule setDiscountStep(int $value)
 * @method int getApplyToShipping()
 * @method Rule setApplyToShipping(int $value)
 * @method int getTimesUsed()
 * @method Rule setTimesUsed(int $value)
 * @method int getIsRss()
 * @method Rule setIsRss(int $value)
 * @method string getWebsiteIds()
 * @method Rule setWebsiteIds(string $value)
 * @method int getCouponType()
 * @method Rule setCouponType(int $value)
 * @method int getUseAutoGeneration()
 * @method Rule setUseAutoGeneration(int $value)
 * @method string getCouponCode()
 * @method Rule setCouponCode(string $value)
 * @method int getRuleId()
 * @method Rule setRuleId(int $ruleId)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Rule extends AbstractModel
{
    /**
     * Coupon types
     */
    public const COUPON_TYPE_NO_COUPON = 1;

    public const COUPON_TYPE_SPECIFIC = 2;

    public const COUPON_TYPE_AUTO = 3;

    /**
     * Rule type actions
     */
    public const TO_PERCENT_ACTION = 'to_percent';

    public const BY_PERCENT_ACTION = 'by_percent';

    public const TO_FIXED_ACTION = 'to_fixed';

    public const BY_FIXED_ACTION = 'by_fixed';

    public const CART_FIXED_ACTION = 'cart_fixed';

    public const BUY_X_GET_Y_ACTION = 'buy_x_get_y';

    /**
     * Store coupon code generator instance
     *
     * @var CouponCodegeneratorInterface
     */
    protected $_couponCodeGenerator;

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

    /**
     * Rule's primary coupon
     *
     * @var Coupon
     */
    protected $_primaryCoupon;

    /**
     * Rule's subordinate coupons
     *
     * @var Coupon[]
     */
    protected $_coupons;

    /**
     * Coupon types cache for lazy getter
     *
     * @var array
     */
    protected $_couponTypes;

    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $_validatedAddresses = [];

    /**
     * @var CouponFactory
     */
    protected $_couponFactory;

    /**
     * @var CodegeneratorFactory
     */
    protected $_codegenFactory;

    /**
     * @var CondCombineFactory
     */
    protected $_condCombineFactory;

    /**
     * @var CondProductCombineFactory
     */
    protected $_condProdCombineF;

    /**
     * @var CouponCollection
     */
    protected $_couponCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Rule constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CouponFactory $couponFactory
     * @param Coupon\CodegeneratorFactory $codegenFactory
     * @param Rule\Condition\CombineFactory $condCombineFactory
     * @param Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param ResourceModel\Coupon\Collection $couponCollection
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json $serializer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CouponFactory $couponFactory,
        CodegeneratorFactory $codegenFactory,
        CondCombineFactory $condCombineFactory,
        CondProductCombineFactory $condProdCombineF,
        CouponCollection $couponCollection,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null
    ) {
        $this->_couponFactory = $couponFactory;
        $this->_codegenFactory = $codegenFactory;
        $this->_condCombineFactory = $condCombineFactory;
        $this->_condProdCombineF = $condProdCombineF;
        $this->_couponCollection = $couponCollection;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceRule::class);
        $this->setIdFieldName('rule_id');
    }

    /**
     * Set coupon code and uses per coupon
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->loadRelations();
        return parent::_afterLoad();
    }

    /**
     * Load all relative data
     *
     * @return void
     */
    public function loadRelations()
    {
        $this->loadCouponCode();
    }

    /**
     * Load coupon code
     *
     * @return void
     */
    public function loadCouponCode()
    {
        $this->setCouponCode($this->getPrimaryCoupon()->getCode());
        if ($this->getUsesPerCoupon() == null && !$this->getUseAutoGeneration()) {
            $this->setUsesPerCoupon($this->getPrimaryCoupon()->getUsageLimit());
        }
    }

    /**
     * Save/delete coupon
     *
     * @return $this
     */
    public function afterSave()
    {
        $couponCode = is_string($this->getCouponCode()) ? trim($this->getCouponCode()) : '';
        if (strlen(
            $couponCode
        ) && $this->getCouponType() == self::COUPON_TYPE_SPECIFIC && !$this->getUseAutoGeneration()
        ) {
            $this->getPrimaryCoupon()->setCode(
                $couponCode
            )->setUsageLimit(
                $this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null
            )->setUsagePerCustomer(
                $this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null
            )->save();
        } else {
            $this->getPrimaryCoupon()->delete();
        }

        parent::afterSave();
        return $this;
    }

    /**
     * Initialize rule model data from array. Set store labels if applicable.
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        parent::loadPost($data);

        if (isset($data['store_labels'])) {
            $this->setStoreLabels($data['store_labels']);
        }

        return $this;
    }

    /**
     * Get rule condition combine model instance
     *
     * @return CondCombine
     */
    public function getConditionsInstance()
    {
        return $this->_condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return CondProductCombine
     */
    public function getActionsInstance()
    {
        return $this->_condProdCombineF->create();
    }

    /**
     * Returns code generator instance for auto generated coupons
     *
     * @return CouponCodegeneratorInterface
     */
    public function getCouponCodeGenerator()
    {
        if (!$this->_couponCodeGenerator) {
            return $this->_codegenFactory->create(['data' => ['length' => 16]]);
        }
        return $this->_couponCodeGenerator;
    }

    /**
     * Set code generator instance for auto generated coupons
     *
     * @param CouponCodegeneratorInterface $codeGenerator
     * @return void
     */
    public function setCouponCodeGenerator(CouponCodegeneratorInterface $codeGenerator)
    {
        $this->_couponCodeGenerator = $codeGenerator;
    }

    /**
     * Retrieve rule's primary coupon
     *
     * @return Coupon
     */
    public function getPrimaryCoupon()
    {
        if ($this->_primaryCoupon === null) {
            $this->_primaryCoupon = $this->_couponFactory->create();
            $this->_primaryCoupon->loadPrimaryByRule($this->getId());
            $this->_primaryCoupon->setRule($this)->setIsPrimary(true);
        }
        return $this->_primaryCoupon;
    }

    /**
     * Get sales rule customer group Ids
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array)$customerGroupIds);
        }
        return $this->_getData('customer_group_ids');
    }

    /**
     * Get Rule label by specified store
     *
     * @param Store|int|bool|null $store
     * @return string|bool
     */
    public function getStoreLabel($store = null)
    {
        $storeId = $this->_storeManager->getStore($store)->getId();
        $labels = (array)$this->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }

    /**
     * Set if not yet and retrieve rule store labels
     *
     * @return array
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $linkedField = $this->_getResource()->getLinkField();
            $labels = $this->_getResource()->getStoreLabels($this->getData($linkedField));
            $this->setStoreLabels($labels);
        }

        return $this->_getData('store_labels');
    }

    /**
     * Retrieve subordinate coupons
     *
     * @return Coupon[]
     */
    public function getCoupons()
    {
        if ($this->_coupons === null) {
            $this->_couponCollection->addRuleToFilter($this);
            $this->_coupons = $this->_couponCollection->getItems();
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
            $this->_couponTypes = [
                Rule::COUPON_TYPE_NO_COUPON => __('No Coupon'),
                Rule::COUPON_TYPE_SPECIFIC => __('Specific Coupon'),
            ];
            $transport = new DataObject(
                ['coupon_types' => $this->_couponTypes, 'is_coupon_type_auto_visible' => false]
            );
            $this->_eventManager->dispatch('salesrule_rule_get_coupon_types', ['transport' => $transport]);
            $this->_couponTypes = $transport->getCouponTypes();
            if ($transport->getIsCouponTypeAutoVisible()) {
                $this->_couponTypes[Rule::COUPON_TYPE_AUTO] = __('Auto');
            }
        }
        return $this->_couponTypes;
    }

    /**
     * Acquire coupon instance
     *
     * @param bool $saveNewlyCreated Whether or not to save newly created coupon
     * @param int $saveAttemptCount Number of attempts to save newly created coupon
     * @return Coupon|null
     * @throws Exception|LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function acquireCoupon($saveNewlyCreated = true, $saveAttemptCount = 10)
    {
        if ($this->getCouponType() == self::COUPON_TYPE_NO_COUPON) {
            return null;
        }
        if ($this->getCouponType() == self::COUPON_TYPE_SPECIFIC) {
            return $this->getPrimaryCoupon();
        }
        /** @var Coupon $coupon */
        $coupon = $this->_couponFactory->create();
        $coupon->setRule(
            $this
        )->setIsPrimary(
            false
        )->setUsageLimit(
            $this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null
        )->setUsagePerCustomer(
            $this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null
        )->setType(
            CouponInterface::TYPE_GENERATED
        );

        $couponCode = self::getCouponCodeGenerator()->generateCode();
        $coupon->setCode($couponCode);

        $ok = false;
        if (!$saveNewlyCreated) {
            $ok = true;
        } else {
            if ($this->getId()) {
                for ($attemptNum = 0; $attemptNum < $saveAttemptCount; $attemptNum++) {
                    try {
                        $coupon->save();
                    } catch (Exception $e) {
                        if ($e instanceof LocalizedException || $coupon->getId()) {
                            throw $e;
                        }
                        $coupon->setCode(
                            $couponCode . self::getCouponCodeGenerator()->getDelimiter() . sprintf(
                                '%04u',
                                random_int(0, 9999)
                            )
                        );
                        continue;
                    }
                    $ok = true;
                    break;
                }
            }
        }
        if (!$ok) {
            throw new LocalizedException(__('Can\'t acquire coupon.'));
        }

        return $coupon;
    }

    /**
     * Get from date.
     *
     * @return string
     * @since 100.1.0
     */
    public function getFromDate()
    {
        return $this->getData('from_date');
    }

    /**
     * Get to date.
     *
     * @return string
     * @since 100.1.0
     */
    public function getToDate()
    {
        return $this->getData('to_date');
    }

    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->_validatedAddresses[$addressId]) ? true : false;
    }

    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     * @return $this
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
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->_validatedAddresses[$addressId]) ? $this->_validatedAddresses[$addressId] : false;
    }

    /**
     * Return id for address
     *
     * @param Address $address
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }
        return $address;
    }

    /**
     * Get conditions field set id.
     *
     * @param string $formName
     * @return string
     * @since 100.1.0
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get actions field set id.
     *
     * @param string $formName
     * @return string
     * @since 100.1.0
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }
}
