<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Api\Data\RuleExtensionInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Helper\Data;
use Magento\CatalogRule\Model\Data\Condition\Converter;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog Rule data model
 *
 * @method Rule setFromDate(string $value)
 * @method Rule setToDate(string $value)
 * @method Rule setCustomerGroupIds(string $value)
 * @method string getWebsiteIds()
 * @method Rule setWebsiteIds(string $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Rule extends AbstractModel implements RuleInterface, IdentityInterface, ResetAfterRequestInterface
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
     * Catalog rule data class
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
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

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
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
     */
    protected $_ruleProductProcessor;

    /**
     * @var Data\Condition\Converter
     */
    protected $ruleConditionConverter;

    /**
     * @var ConditionsToCollectionApplier
     */
    private $conditionsToCollectionApplier;

    /**
     * @var array
     */
    private $websitesMap;

    /**
     * @var RuleResourceModel
     */
    private $ruleResourceModel;

    /**
     * Rule constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Rule\Condition\CombineFactory $combineFactory
     * @param Rule\Action\CollectionFactory $actionCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogRule\Helper\Data $catalogRuleData
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param Indexer\Rule\RuleProductProcessor $ruleProductProcessor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $relatedCacheTypes
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleResourceModel|null $ruleResourceModel
     * @param ConditionsToCollectionApplier $conditionsToCollectionApplier
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        CombineFactory $combineFactory,
        RuleCollectionFactory $actionCollectionFactory,
        ProductFactory $productFactory,
        Iterator $resourceIterator,
        Session $customerSession,
        Data $catalogRuleData,
        TypeListInterface $cacheTypesList,
        DateTime $dateTime,
        RuleProductProcessor $ruleProductProcessor,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = [],
        ?ExtensionAttributesFactory $extensionFactory = null,
        ?AttributeValueFactory $customAttributeFactory = null,
        ?Json $serializer = null,
        ?RuleResourceModel $ruleResourceModel = null,
        ?ConditionsToCollectionApplier $conditionsToCollectionApplier = null
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
        $this->ruleResourceModel = $ruleResourceModel ?: ObjectManager::getInstance()->get(RuleResourceModel::class);

        $this->conditionsToCollectionApplier = $conditionsToCollectionApplier
            ?? ObjectManager::getInstance()->get(ConditionsToCollectionApplier::class);

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
     * Init resource model and id field
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(RuleResourceModel::class);
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
            $customerGroupIds = $this->ruleResourceModel->getCustomerGroupIds($this->getId());
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
            return (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
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
                $productCollection->setStoreId($this->_storeManager->getDefaultStoreView()->getId());
                $productCollection->addWebsiteFilter($this->getWebsiteIds());
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                if ($this->canPreMapProducts()) {
                    $productCollection = $this->conditionsToCollectionApplier
                        ->applyConditionsToCollection($this->getConditions(), $productCollection);
                }

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
     * Check if we can use mapping for rule conditions
     *
     * @return bool
     */
    private function canPreMapProducts()
    {
        $conditions = $this->getConditions();

        // No need to map products if there is no conditions in rule
        if (!$conditions || !$conditions->getConditions()) {
            return false;
        }

        return true;
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
        $websiteIds = $this->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            if (!in_array($websiteId, $websiteIds)) {
                continue;
            }
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
        if ($this->websitesMap === null) {
            $this->websitesMap = [];
            $websites = $this->_storeManager->getWebsites();
            foreach ($websites as $website) {
                // Continue if website has no store to be able to create catalog rule for website without store
                if ($website->getDefaultStore() === null) {
                    continue;
                }
                $this->websitesMap[$website->getId()] = $website->getDefaultStore()->getId();
            }
        }

        return $this->websitesMap;
    }

    /**
     * @inheritdoc
     */
    public function validateData(DataObject $dataObject)
    {
        $result = parent::validateData($dataObject);
        if ($result === true) {
            $result = [];
        }

        $action = $dataObject->getData('simple_action');
        $discount = $dataObject->getData('discount_amount');
        $result = array_merge($result, $this->validateDiscount($action, $discount));

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
                }
                break;
            case 'by_fixed':
            case 'to_fixed':
                if ($discount < 0) {
                    $result[] = __('Discount value should be 0 or greater.');
                }
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
                        $priceRules = $priceRules ? $priceRules : $price;
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
        return $this->ruleResourceModel->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
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
     * @inheritdoc
     *
     * @return $this
     */
    public function afterSave()
    {
        if (!$this->getIsActive() && !$this->getOrigData(self::IS_ACTIVE)) {
            return parent::afterSave();
        }

        if (!$this->_ruleProductProcessor->isIndexerScheduled()) {
            $this->ruleResourceModel->addCommitCallback([$this, 'reindex']);
        }

        return parent::afterSave();
    }

    /**
     * Init indexing process after rule save
     *
     * @return void
     */
    public function reindex()
    {
        $this->_ruleProductProcessor->reindexRow($this->getRuleId());
    }

    /**
     * @inheritdoc
     *
     * @return $this
     */
    public function afterDelete()
    {
        if ($this->getIsActive() && !$this->_ruleProductProcessor->isIndexerScheduled()) {
            $this->ruleResourceModel->addCommitCallback([$this, 'reindex']);
        }
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
     *
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
                if ($value != $array2[$key]) {
                    $result[$key] = true;
                }
            } else {
                $result[$key] = true;
            }
        }
        return $result;
    }

    /**
     * Getter for conditions field set ID
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRuleId($ruleId)
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritdoc
     */
    public function getRuleCondition()
    {
        return $this->getRuleConditionConverter()->arrayToDataModel($this->getConditions()->asArray());
    }

    /**
     * @inheritdoc
     */
    public function setRuleCondition($condition)
    {
        $this->getConditions()
            ->setConditions([])
            ->loadArray($this->getRuleConditionConverter()->dataModelToArray($condition));
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStopRulesProcessing()
    {
        return $this->getData(self::STOP_RULES_PROCESSING);
    }

    /**
     * @inheritdoc
     */
    public function setStopRulesProcessing($isStopProcessing)
    {
        return $this->setData(self::STOP_RULES_PROCESSING, $isStopProcessing);
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @inheritdoc
     */
    public function getSimpleAction()
    {
        return $this->getData(self::SIMPLE_ACTION);
    }

    /**
     * @inheritdoc
     */
    public function setSimpleAction($action)
    {
        return $this->setData(self::SIMPLE_ACTION, $action);
    }

    /**
     * @inheritdoc
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::DISCOUNT_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountAmount($amount)
    {
        return $this->setData(self::DISCOUNT_AMOUNT, $amount);
    }

    /**
     * Get from date
     *
     * @return string
     */
    public function getFromDate()
    {
        return $this->getData('from_date');
    }

    /**
     * Get to date
     *
     * @return string
     */
    public function getToDate()
    {
        return $this->getData('to_date');
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\CatalogRule\Api\Data\RuleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\CatalogRule\Api\Data\RuleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(RuleExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Getter for the rule condition converter
     *
     * @return Data\Condition\Converter
     * @deprecated 100.1.0
     * @see getRuleCondition, setRuleCondition
     */
    private function getRuleConditionConverter()
    {
        if (null === $this->ruleConditionConverter) {
            $this->ruleConditionConverter = ObjectManager::getInstance()
                ->get(Converter::class);
        }
        return $this->ruleConditionConverter;
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return ['price'];
    }

    /**
     * Clear price rules cache.
     *
     * @return void;
     */
    public function clearPriceRulesData(): void
    {
        self::$_priceRulesData = [];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        self::$_priceRulesData = [];
    }
}
