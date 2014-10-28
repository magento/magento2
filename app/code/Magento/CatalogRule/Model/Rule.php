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
namespace Magento\CatalogRule\Model;

use Magento\Catalog\Model\Product;

/**
 * Catalog Rule data model
 *
 * @method \Magento\CatalogRule\Model\Resource\Rule _getResource()
 * @method \Magento\CatalogRule\Model\Resource\Rule getResource()
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
 */
class Rule extends \Magento\Rule\Model\AbstractModel
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
    protected static $_priceRulesData = array();

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
     * @var \Magento\Framework\Model\Resource\Iterator
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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory
     * @param \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\Resource\Iterator $resourceIterator
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogRule\Helper\Data $catalogRuleData
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $relatedCacheTypes
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\Resource\Iterator $resourceIterator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypesList,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $relatedCacheTypes = array(),
        array $data = array()
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
        $this->_init('Magento\CatalogRule\Model\Resource\Rule');
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
            return $this->dateTime->now();
        }
        return $this->_now;
    }

    /**
     * Set current date for current rule
     *
     * @param string $now
     * @return void
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
        if (is_null($this->_productIds)) {
            $this->_productIds = array();
            $this->setCollectedAttributes(array());

            if ($this->getWebsiteIds()) {
                /** @var $productCollection \Magento\Catalog\Model\Resource\Product\Collection */
                $productCollection = $this->_productCollectionFactory->create();
                $productCollection->addWebsiteFilter($this->getWebsiteIds());
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->_resourceIterator->walk(
                    $productCollection->getSelect(),
                    array(array($this, 'callbackValidateProduct')),
                    array(
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->_productFactory->create()
                    )
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
        $results = array();

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
        $map = array();
        $websites = $this->_storeManager->getWebsites(true);
        foreach ($websites as $website) {
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    /**
     * Apply rule to product
     *
     * @param int|Product $product
     * @param array|null $websiteIds
     *
     * @return void
     */
    public function applyToProduct($product, $websiteIds = null)
    {
        if (is_numeric($product)) {
            $product = $this->_productFactory->create()->load($product);
        }
        if (is_null($websiteIds)) {
            $websiteIds = $this->getWebsiteIds();
        }
        $this->getResource()->applyToProduct($this, $product, $websiteIds);
    }

    /**
     * Apply all price rules, invalidate related cache and refresh price index
     *
     * @return void
     */
    public function applyAll()
    {
        $this->getResourceCollection()->walk(array($this->_getResource(), 'updateRuleProductData'));
        $this->_getResource()->applyAllRulesForDateRange();
        $this->_invalidateCache();
    }

    /**
     * Apply all price rules to product
     *
     * @param  int|Product $product
     * @return void
     */
    public function applyAllRulesToProduct($product)
    {
        $this->_getResource()->applyAllRulesForDateRange(null, null, $product);
        $this->_invalidateCache();
    }

    /**
     * Calculate price using catalog price rule of product
     *
     * @param Product $product
     * @param float $price
     * @return float|null
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
     */
    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    /**
     * Returns products filter
     *
     * @return array|int|null
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
     * @deprecated after 1.11.2.0
     *
     * @param string $format
     *
     * @return string
     */
    public function toString($format = '')
    {
        return '';
    }

    /**
     * Returns rule as an array for admin interface
     *
     * @deprecated after 1.11.2.0
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
