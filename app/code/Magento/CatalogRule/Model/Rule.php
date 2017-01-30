<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model;

use Magento\Catalog\Model\Product;

/**
 * Catalog Rule data model
 *
 * @method \Magento\CatalogRule\Model\ResourceModel\Rule _getResource()
 * @method \Magento\CatalogRule\Model\ResourceModel\Rule getResource()
 * @method string getName()
 * @method \Magento\CatalogRule\Model\Rule setName(string $value)
 * @method string getDescription()
 * @method \Magento\CatalogRule\Model\Rule setDescription(string $value)
 * @method string getFromDate()
 * @method \Magento\CatalogRule\Model\Rule setFromDate(string $value)
 * @method string getToDate()
 * @method \Magento\CatalogRule\Model\Rule setToDate(string $value)
 * @method \Magento\CatalogRule\Model\Rule setCustomerGroupIds(string $value)
 * @method int getIsActive()
 * @method \Magento\CatalogRule\Model\Rule setIsActive(int $value)
 * @method string getConditionsSerialized()
 * @method \Magento\CatalogRule\Model\Rule setConditionsSerialized(string $value)
 * @method string getActionsSerialized()
 * @method \Magento\CatalogRule\Model\Rule setActionsSerialized(string $value)
 * @method int getStopRulesProcessing()
 * @method \Magento\CatalogRule\Model\Rule setStopRulesProcessing(int $value)
 * @method int getSortOrder()
 * @method \Magento\CatalogRule\Model\Rule setSortOrder(int $value)
 * @method string getSimpleAction()
 * @method \Magento\CatalogRule\Model\Rule setSimpleAction(string $value)
 * @method float getDiscountAmount()
 * @method \Magento\CatalogRule\Model\Rule setDiscountAmount(float $value)
 * @method string getWebsiteIds()
 * @method \Magento\CatalogRule\Model\Rule setWebsiteIds(string $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rule extends \Magento\Rule\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'catalogrule_rule';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $_productsFilter = null;

    /**
     * Store current date at "Y-m-d H:i:s" format
     *
     * @var string
     */
    protected $_now;

    /**
     * Cached data of prices calculated by price rules
     *
     * @var array
     */
    protected static $_priceRulesData = [];

    /**
     * Catalog rule data
     *
     * @var \Magento\CatalogRule\Helper\Data
     */
    protected $_catalogRuleData;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypesList;

    /**
     * @var array
     */
    protected $_relatedCacheTypes;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $_resourceIterator;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $_combineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $_actionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
     */
    protected $_ruleProductProcessor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory
     * @param \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogRule\Helper\Data $catalogRuleData
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor $ruleProductProcessor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $relatedCacheTypes
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor $ruleProductProcessor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->_resourceIterator = $resourceIterator;
        $this->_customerSession = $customerSession;
        $this->_catalogRuleData = $catalogRuleData;
        $this->_cacheTypesList = $cacheTypesList;
        $this->_relatedCacheTypes = $relatedCacheTypes;
        $this->dateTime = $dateTime;
        $this->_ruleProductProcessor = $ruleProductProcessor;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Init resource model and id field
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\CatalogRule\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    /**
     * Get catalog rule customer group Ids
     *
     * @return array|null
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
     * Retrieve current date for current rule
     *
     * @return string
     */
    public function getNow()
    {
        if (!$this->_now) {
            return (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }
        return $this->_now;
    }

    /**
     * Set current date for current rule
     *
     * @param string $now
     * @return void
     * @codeCoverageIgnore
     */
    public function setNow($now)
    {
        $this->_now = $now;
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            if ($this->getWebsiteIds()) {
                /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
                $productCollection = $this->_productCollectionFactory->create();
                $productCollection->addWebsiteFilter($this->getWebsiteIds());
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->_resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->_productFactory->create()
                    ]
                );
            }
        }

        return $this->_productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->_getWebsitesMap();
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
        }
        $this->_productIds[$product->getId()] = $results;
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = $this->_storeManager->getWebsites(true);
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    /**
     * {@inheritdoc}
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = parent::validateData($dataObject);
        if ($result === true) {
            $result = [];
        }

        $action = $dataObject->getData('simple_action');
        $discount = $dataObject->getData('discount_amount');
        $result = array_merge($result, $this->validateDiscount($action, $discount));
        if ($dataObject->getData('sub_is_enable') == 1) {
            $action = $dataObject->getData('sub_simple_action');
            $discount = $dataObject->getData('sub_discount_amount');
            $result = array_merge($result, $this->validateDiscount($action, $discount));
        }

        return !empty($result) ? $result : true;
    }

    /**
     * Validate discount based on action
     *
     * @param string $action
     * @param string|int|float $discount
     *
     * @return array Validation errors
     */
    protected function validateDiscount($action, $discount)
    {
        $result = [];
        switch ($action) {
            case 'by_percent':
            case 'to_percent':
                if ($discount < 0 || $discount > 100) {
                    $result[] = __('Percentage discount should be between 0 and 100.');
                };
                break;
            case 'by_fixed':
            case 'to_fixed':
                if ($discount < 0) {
                    $result[] = __('Discount value should be 0 or greater.');
                };
                break;
            default:
                $result[] = __('Unknown action.');
        }
        return $result;
    }

    /**
     * Calculate price using catalog price rule of product
     *
     * @param Product $product
     * @param float $price
     * @return float|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function calcProductPriceRule(Product $product, $price)
    {
        $priceRules = null;
        $productId = $product->getId();
        $storeId = $product->getStoreId();
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
        }
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        $cacheKey = date('Y-m-d', $dateTs) . "|{$websiteId}|{$customerGroupId}|{$productId}|{$price}";

        if (!array_key_exists($cacheKey, self::$_priceRulesData)) {
            $rulesData = $this->_getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
            if ($rulesData) {
                foreach ($rulesData as $ruleData) {
                    if ($product->getParentId()) {
                        if (!empty($ruleData['sub_simple_action'])) {
                            $priceRules = $this->_catalogRuleData->calcPriceRule(
                                $ruleData['sub_simple_action'],
                                $ruleData['sub_discount_amount'],
                                $priceRules ? $priceRules : $price
                            );
                        } else {
                            $priceRules = $priceRules ? $priceRules : $price;
                        }
                        if ($ruleData['action_stop']) {
                            break;
                        }
                    } else {
                        $priceRules = $this->_catalogRuleData->calcPriceRule(
                            $ruleData['action_operator'],
                            $ruleData['action_amount'],
                            $priceRules ? $priceRules : $price
                        );
                        if ($ruleData['action_stop']) {
                            break;
                        }
                    }
                }
                return self::$_priceRulesData[$cacheKey] = $priceRules;
            } else {
                self::$_priceRulesData[$cacheKey] = null;
            }
        } else {
            return self::$_priceRulesData[$cacheKey];
        }
        return null;
    }

    /**
     * Get rules from product
     *
     * @param string $dateTs
     * @param int $websiteId
     * @param array $customerGroupId
     * @param int $productId
     * @return array
     */
    protected function _getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId)
    {
        return $this->_getResource()->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
    }

    /**
     * Filtering products that must be checked for matching with rule
     *
     * @param  int|array $productIds
     * @return void
     * @codeCoverageIgnore
     */
    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    /**
     * Returns products filter
     *
     * @return array|int|null
     * @codeCoverageIgnore
     */
    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }

    /**
     * Invalidate related cache types
     *
     * @return $this
     */
    protected function _invalidateCache()
    {
        if (count($this->_relatedCacheTypes)) {
            $this->_cacheTypesList->invalidate($this->_relatedCacheTypes);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isObjectNew()) {
            $this->getMatchingProductIds();
            if (!empty($this->_productIds) && is_array($this->_productIds)) {
                $this->_ruleProductProcessor->reindexList($this->_productIds);
            }
        } else {
            $this->_ruleProductProcessor->getIndexer()->invalidate();
        }
        return parent::afterSave();
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function afterDelete()
    {
        $this->_ruleProductProcessor->getIndexer()->invalidate();
        return parent::afterDelete();
    }

    /**
     * Check if rule behavior changed
     *
     * @return bool
     */
    public function isRuleBehaviorChanged()
    {
        if (!$this->isObjectNew()) {
            $arrayDiff = $this->dataDiff($this->getOrigData(), $this->getStoredData());
            unset($arrayDiff['name']);
            unset($arrayDiff['description']);
            if (empty($arrayDiff)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get array with data differences
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function dataDiff($array1, $array2)
    {
        $result = [];
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    if ($value != $array2[$key]) {
                        $result[$key] = true;
                    }
                } else {
                    if ($value != $array2[$key]) {
                        $result[$key] = true;
                    }
                }
            } else {
                $result[$key] = true;
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return ['price'];
    }
}
