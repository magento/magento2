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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Shopping Cart Rule data model
 *
 * @method \Magento\SalesRule\Model\Resource\Rule _getResource()
 * @method \Magento\SalesRule\Model\Resource\Rule getResource()
 * @method string getName()
 * @method \Magento\SalesRule\Model\Rule setName(string $value)
 * @method string getDescription()
 * @method \Magento\SalesRule\Model\Rule setDescription(string $value)
 * @method string getFromDate()
 * @method \Magento\SalesRule\Model\Rule setFromDate(string $value)
 * @method string getToDate()
 * @method \Magento\SalesRule\Model\Rule setToDate(string $value)
 * @method int getUsesPerCustomer()
 * @method \Magento\SalesRule\Model\Rule setUsesPerCustomer(int $value)
 * @method int getUsesPerCoupon()
 * @method \Magento\SalesRule\Model\Rule setUsesPerCoupon(int $value)
 * @method \Magento\SalesRule\Model\Rule setCustomerGroupIds(string $value)
 * @method int getIsActive()
 * @method \Magento\SalesRule\Model\Rule setIsActive(int $value)
 * @method string getConditionsSerialized()
 * @method \Magento\SalesRule\Model\Rule setConditionsSerialized(string $value)
 * @method string getActionsSerialized()
 * @method \Magento\SalesRule\Model\Rule setActionsSerialized(string $value)
 * @method int getStopRulesProcessing()
 * @method \Magento\SalesRule\Model\Rule setStopRulesProcessing(int $value)
 * @method int getIsAdvanced()
 * @method \Magento\SalesRule\Model\Rule setIsAdvanced(int $value)
 * @method string getProductIds()
 * @method \Magento\SalesRule\Model\Rule setProductIds(string $value)
 * @method int getSortOrder()
 * @method \Magento\SalesRule\Model\Rule setSortOrder(int $value)
 * @method string getSimpleAction()
 * @method \Magento\SalesRule\Model\Rule setSimpleAction(string $value)
 * @method float getDiscountAmount()
 * @method \Magento\SalesRule\Model\Rule setDiscountAmount(float $value)
 * @method float getDiscountQty()
 * @method \Magento\SalesRule\Model\Rule setDiscountQty(float $value)
 * @method int getDiscountStep()
 * @method \Magento\SalesRule\Model\Rule setDiscountStep(int $value)
 * @method int getSimpleFreeShipping()
 * @method \Magento\SalesRule\Model\Rule setSimpleFreeShipping(int $value)
 * @method int getApplyToShipping()
 * @method \Magento\SalesRule\Model\Rule setApplyToShipping(int $value)
 * @method int getTimesUsed()
 * @method \Magento\SalesRule\Model\Rule setTimesUsed(int $value)
 * @method int getIsRss()
 * @method \Magento\SalesRule\Model\Rule setIsRss(int $value)
 * @method string getWebsiteIds()
 * @method \Magento\SalesRule\Model\Rule setWebsiteIds(string $value)
 * @method int getCouponType()
 * @method \Magento\SalesRule\Model\Rule setCouponType(int $value)
 * @method int getUseAutoGeneration()
 * @method \Magento\SalesRule\Model\Rule setUseAutoGeneration(int $value)
 * @method string getCouponCode()
 * @method \Magento\SalesRule\Model\Rule setCouponCode(string $value)
 *
 * @category    Magento
 * @package     Magento_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\SalesRule\Model;

class Rule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Free Shipping option "For matching items only"
     */
    const FREE_SHIPPING_ITEM    = 1;

    /**
     * Free Shipping option "For shipment with matching items"
     */
    const FREE_SHIPPING_ADDRESS = 2;

    /**
     * Coupon types
     */
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
     * Store coupon code generator instance
     *
     * @var \Magento\SalesRule\Model\Coupon\CodegeneratorInterface
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
     * Contain sores labels
     *
     * @deprecated after 1.6.2.0
     *
     * @var array
     */
    protected $_labels = array();

    /**
     * Rule's primary coupon
     *
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $_primaryCoupon;

    /**
     * Rule's subordinate coupons
     *
     * @var array of \Magento\SalesRule\Model\Coupon
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
    protected $_validatedAddresses = array();

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $_couponFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon\CodegeneratorFactory
     */
    protected $_codegenFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    protected $_condCombineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $_condProdCombineF;

    /**
     * @var \Magento\SalesRule\Model\Resource\Coupon\Collection
     */
    protected $_couponCollection;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\SalesRule\Model\Resource\Coupon\Collection $couponCollection
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\Resource\Coupon\Collection $couponCollection,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_couponFactory = $couponFactory;
        $this->_codegenFactory = $codegenFactory;
        $this->_condCombineFactory = $condCombineFactory;
        $this->_condProdCombineF = $condProdCombineF;
        $this->_couponCollection = $couponCollection;
        $this->_storeManager = $storeManager;
        parent::__construct($formFactory, $context, $registry, $locale, $resource, $resourceCollection, $data);
    }

    /**
     * Set resource model and Id field name
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\SalesRule\Model\Resource\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Set coupon code and uses per coupon
     *
     * @return \Magento\SalesRule\Model\Rule
     */
    protected function _afterLoad()
    {
        $this->setCouponCode($this->getPrimaryCoupon()->getCode());
        if ($this->getUsesPerCoupon() !== null && !$this->getUseAutoGeneration()) {
            $this->setUsesPerCoupon($this->getPrimaryCoupon()->getUsageLimit());
        }
        return parent::_afterLoad();
    }

    /**
     * Save/delete coupon
     *
     * @return \Magento\SalesRule\Model\Rule
     */
    protected function _afterSave()
    {
        $couponCode = trim($this->getCouponCode());
        if (strlen($couponCode)
            && $this->getCouponType() == self::COUPON_TYPE_SPECIFIC
            && !$this->getUseAutoGeneration()
        ) {
            $this->getPrimaryCoupon()
                ->setCode($couponCode)
                ->setUsageLimit($this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null)
                ->setUsagePerCustomer($this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null)
                ->setExpirationDate($this->getToDate())
                ->save();
        } else {
            $this->getPrimaryCoupon()->delete();
        }

        parent::_afterSave();
        return $this;
    }

    /**
     * Initialize rule model data from array.
     * Set store labels if applicable.
     *
     * @param array $data
     *
     * @return \Magento\SalesRule\Model\Rule
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
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->_condProdCombineF->create();
    }

    /**
     * Returns code generator instance for auto generated coupons
     *
     * @return \Magento\SalesRule\Model\Coupon\CodegeneratorInterface
     */
    public function getCouponCodeGenerator()
    {
        if (!$this->_couponCodeGenerator) {
            return $this->_codegenFactory->create(array('data' => array('length' => 16)));
        }
        return $this->_couponCodeGenerator;
    }

    /**
     * Set code generator instance for auto generated coupons
     *
     * @param \Magento\SalesRule\Model\Coupon\CodegeneratorInterface
     */
    public function setCouponCodeGenerator(\Magento\SalesRule\Model\Coupon\CodegeneratorInterface $codeGenerator)
    {
        $this->_couponCodeGenerator = $codeGenerator;
    }

    /**
     * Retrieve rule's primary coupon
     *
     * @return \Magento\SalesRule\Model\Coupon
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
     * @param \Magento\Core\Model\Store|int|bool|null $store
     *
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
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }

        return $this->_getData('store_labels');
    }

    /**
     * Retrieve subordinate coupons
     *
     * @return array of \Magento\SalesRule\Model\Coupon
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
            $this->_couponTypes = array(
                \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON => __('No Coupon'),
                \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC  => __('Specific Coupon'),
            );
            $transport = new \Magento\Object(array(
                'coupon_types'                => $this->_couponTypes,
                'is_coupon_type_auto_visible' => false
            ));
            $this->_eventManager->dispatch('salesrule_rule_get_coupon_types', array('transport' => $transport));
            $this->_couponTypes = $transport->getCouponTypes();
            if ($transport->getIsCouponTypeAutoVisible()) {
                $this->_couponTypes[\Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO] = __('Auto');
            }
        }
        return $this->_couponTypes;
    }

    /**
     * Acquire coupon instance
     *
     * @param bool $saveNewlyCreated Whether or not to save newly created coupon
     * @param int $saveAttemptCount Number of attempts to save newly created coupon
     * @return \Magento\SalesRule\Model\Coupon|null
     * @throws \Exception|\Magento\Core\Exception
     */
    public function acquireCoupon($saveNewlyCreated = true, $saveAttemptCount = 10)
    {
        if ($this->getCouponType() == self::COUPON_TYPE_NO_COUPON) {
            return null;
        }
        if ($this->getCouponType() == self::COUPON_TYPE_SPECIFIC) {
            return $this->getPrimaryCoupon();
        }
        /** @var \Magento\SalesRule\Model\Coupon $coupon */
        $coupon = $this->_couponFactory->create();
        $coupon->setRule($this)
            ->setIsPrimary(false)
            ->setUsageLimit($this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null)
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
                } catch (\Exception $e) {
                    if ($e instanceof \Magento\Core\Exception || $coupon->getId()) {
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
            throw new \Magento\Core\Exception(__('Can\'t acquire coupon.'));
        }

        return $coupon;
    }

    /**
     * Check cached validation result for specific address
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
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
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   bool $validationResult
     * @return  \Magento\SalesRule\Model\Rule
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
     * @param   \Magento\Sales\Model\Quote\Address $address
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
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  string
     */
    private function _getAddressId($address) {
        if($address instanceof \Magento\Sales\Model\Quote\Address) {
            return $address->getId();
        }
        return $address;
    }





    /**
     * Collect all product attributes used in serialized rule's action or condition
     *
     * @deprecated after 1.6.2.0 use \Magento\SalesRule\Model\Resource\Rule::getProductAttributes() instead
     *
     * @param string $serializedString
     *
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        return $this->_getResource()->getProductAttributes($serializedString);
    }

    /**
     * @deprecated after 1.6.2.0
     *
     * @param string $format
     *
     * @return string
     */
    public function toString($format='')
    {
        return '';
    }

    /**
     * Returns rule as an array for admin interface
     *
     * @deprecated after 1.6.2.0
     *
     * @param array $arrAttributes
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
        return parent::toArray($arrAttributes);
    }
}
