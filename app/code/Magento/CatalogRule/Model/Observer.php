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
 * @package     Magento_CatalogRule
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Price rules observer model
 */
namespace Magento\CatalogRule\Model;

class Observer
{
    /**
     * Store calculated catalog rules prices for products
     * Prices collected per website, customer group, date and product
     *
     * @var array
     */
    protected $_rulePrices = array();

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Product\Price
     */
    protected $_productPrice;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var \Magento\CatalogRule\Model\FlagFactory
     */
    protected $_flagFactory;

    /**
     * @var \Magento\CatalogRule\Model\Resource\Rule\CollectionFactory
     */
    protected $_ruleCollFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\CatalogRule\Model\Resource\RuleFactory
     */
    protected $_resourceRuleFactory;

    /**
     * @var \Magento\CatalogRule\Model\Resource\Rule
     */
    protected $_resourceRule;

    /**
     * @param \Magento\CatalogRule\Model\Resource\RuleFactory $resourceRuleFactory
     * @param \Magento\CatalogRule\Model\Resource\Rule $resourceRule
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\CatalogRule\Model\RuleFactory $ruleFactory
     * @param \Magento\CatalogRule\Model\Resource\Rule\CollectionFactory $ruleCollFactory
     * @param \Magento\CatalogRule\Model\FlagFactory $flagFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\CatalogRule\Model\Rule\Product\Price $productPrice
     * @param \Magento\Core\Model\Registry $coreRegistry
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\CatalogRule\Model\Resource\RuleFactory $resourceRuleFactory,
        \Magento\CatalogRule\Model\Resource\Rule $resourceRule,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory,
        \Magento\CatalogRule\Model\Resource\Rule\CollectionFactory $ruleCollFactory,
        \Magento\CatalogRule\Model\FlagFactory $flagFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\CatalogRule\Model\Rule\Product\Price $productPrice,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_resourceRule = $resourceRule;
        $this->_resourceRuleFactory = $resourceRuleFactory;
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
        $this->_ruleFactory = $ruleFactory;
        $this->_flagFactory = $flagFactory;
        $this->_ruleCollFactory = $ruleCollFactory;
        $this->_customerSession = $customerSession;
        $this->_backendSession = $backendSession;
        $this->_productPrice = $productPrice;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Apply all catalog price rules for specific product
     *
     * @param   \Magento\Event\Observer $observer
     * @return  \Magento\CatalogRule\Model\Observer
     */
    public function applyAllRulesOnProduct($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getIsMassupdate()) {
            return;
        }

        $productWebsiteIds = $product->getWebsiteIds();

        $rules = $this->_ruleCollFactory->create()
            ->addFieldToFilter('is_active', 1);

        foreach ($rules as $rule) {
            $websiteIds = array_intersect($productWebsiteIds, $rule->getWebsiteIds());
            $rule->applyToProduct($product, $websiteIds);
        }

        return $this;
    }

    /**
     * Apply all price rules for current date.
     * Handle cataolg_product_import_after event
     *
     * @param   \Magento\Event\Observer $observer
     *
     * @return  \Magento\CatalogRule\Model\Observer
     */
    public function applyAllRules($observer)
    {
        $this->_resourceRule->applyAllRulesForDateRange($this->_resourceRule->formatDate(mktime(0,0,0)));
        $this->_flagFactory->create()
            ->loadSelf()
            ->setState(0)
            ->save();

        return $this;
    }

    /**
     * Apply all catalog price rules
     *
     * Fire the same name process as catalog rule model
     * Event name "apply_catalog_price_rules"
     *
     * @param  \Magento\Event\Observer $observer
     * @return \Magento\CatalogRule\Model\Observer
     */
    public function processApplyAll(\Magento\Event\Observer $observer)
    {
        $this->_ruleFactory->create()->applyAll();
        $this->_flagFactory->create()
            ->loadSelf()
            ->setState(0)
            ->save();
        return $this;
    }

    /**
     * Apply catalog price rules to product on frontend
     *
     * @param   \Magento\Event\Observer $observer
     *
     * @return  \Magento\CatalogRule\Model\Observer
     */
    public function processFrontFinalPrice($observer)
    {
        $product    = $observer->getEvent()->getProduct();
        $pId        = $product->getId();
        $storeId    = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = $observer->getEvent()->getDate();
        } else {
            $date = $this->_locale->storeTimeStamp($storeId);
        }

        if ($observer->hasWebsiteId()) {
            $wId = $observer->getEvent()->getWebsiteId();
        } else {
            $wId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        }

        if ($observer->hasCustomerGroupId()) {
            $gId = $observer->getEvent()->getCustomerGroupId();
        } elseif ($product->hasCustomerGroupId()) {
            $gId = $product->getCustomerGroupId();
        } else {
            $gId = $this->_customerSession->getCustomerGroupId();
        }

        $key = "$date|$wId|$gId|$pId";
        if (!isset($this->_rulePrices[$key])) {
            $rulePrice = $this->_resourceRuleFactory->create()
                ->getRulePrice($date, $wId, $gId, $pId);
            $this->_rulePrices[$key] = $rulePrice;
        }
        if ($this->_rulePrices[$key]!==false) {
            $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }

    /**
     * Apply catalog price rules to product in admin
     *
     * @param   \Magento\Event\Observer $observer
     *
     * @return  \Magento\CatalogRule\Model\Observer
     */
    public function processAdminFinalPrice($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeId = $product->getStoreId();
        $date = $this->_locale->storeDate($storeId);
        $key = false;

        $ruleData = $this->_coreRegistry->registry('rule_data');
        if ($ruleData) {
            $wId = $ruleData->getWebsiteId();
            $gId = $ruleData->getCustomerGroupId();
            $pId = $product->getId();

            $key = "$date|$wId|$gId|$pId";
        } elseif (!is_null($product->getWebsiteId()) && !is_null($product->getCustomerGroupId())) {
            $wId = $product->getWebsiteId();
            $gId = $product->getCustomerGroupId();
            $pId = $product->getId();
            $key = "$date|$wId|$gId|$pId";
        }

        if ($key) {
            if (!isset($this->_rulePrices[$key])) {
                $rulePrice = $this->_resourceRuleFactory->create()
                    ->getRulePrice($date, $wId, $gId, $pId);
                $this->_rulePrices[$key] = $rulePrice;
            }
            if ($this->_rulePrices[$key]!==false) {
                $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                $product->setFinalPrice($finalPrice);
            }
        }

        return $this;
    }

    /**
     * Calculate price using catalog price rules of configurable product
     *
     * @param \Magento\Event\Observer $observer
     *
     * @return \Magento\CatalogRule\Model\Observer
     */
    public function catalogProductTypeConfigurablePrice(\Magento\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof \Magento\Catalog\Model\Product
            && $product->getConfigurablePrice() !== null
        ) {
            $configurablePrice = $product->getConfigurablePrice();
            $productPriceRule = $this->_ruleFactory->create()
                ->calcProductPriceRule($product, $configurablePrice);
            if ($productPriceRule !== null) {
                $product->setConfigurablePrice($productPriceRule);
            }
        }

        return $this;
    }

    /**
     * Daily update catalog price rule by cron
     * Update include interval 3 days - current day - 1 days before + 1 days after
     * This method is called from cron process, cron is working in UTC time and
     * we should generate data for interval -1 day ... +1 day
     *
     * @param   \Magento\Event\Observer $observer
     *
     * @return  \Magento\CatalogRule\Model\Observer
     */
    public function dailyCatalogUpdate($observer)
    {
        $this->_resourceRule->applyAllRulesForDateRange();

        return $this;
    }

    /**
     * Clean out calculated catalog rule prices for products
     */
    public function flushPriceCache()
    {
        $this->_rulePrices = array();
    }

    /**
     * Calculate minimal final price with catalog rule price
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\CatalogRule\Model\Observer
     */
    public function prepareCatalogProductPriceIndexTable(\Magento\Event\Observer $observer)
    {
        $select             = $observer->getEvent()->getSelect();

        $indexTable         = $observer->getEvent()->getIndexTable();
        $entityId           = $observer->getEvent()->getEntityId();
        $customerGroupId    = $observer->getEvent()->getCustomerGroupId();
        $websiteId          = $observer->getEvent()->getWebsiteId();
        $websiteDate        = $observer->getEvent()->getWebsiteDate();
        $updateFields       = $observer->getEvent()->getUpdateFields();

        $this->_productPrice->applyPriceRuleToIndexTable(
            $select, $indexTable, $entityId, $customerGroupId, $websiteId, $updateFields, $websiteDate
        );

        return $this;
    }

    /**
     * Check rules that contains affected attribute
     * If rules were found they will be set to inactive and notice will be add to admin session
     *
     * @param string $attributeCode
     *
     * @return \Magento\CatalogRule\Model\Observer
     */
    protected function _checkCatalogRulesAvailability($attributeCode)
    {
        /* @var $collection \Magento\CatalogRule\Model\Resource\Rule\Collection */
        $collection = $this->_ruleCollFactory->create()
            ->addAttributeInConditionFilter($attributeCode);

        $disabledRulesCount = 0;
        foreach ($collection as $rule) {
            /* @var $rule \Magento\CatalogRule\Model\Rule */
            $rule->setIsActive(0);
            /* @var $rule->getConditions() \Magento\CatalogRule\Model\Rule\Condition\Combine */
            $this->_removeAttributeFromConditions($rule->getConditions(), $attributeCode);
            $rule->save();

            $disabledRulesCount++;
        }

        if ($disabledRulesCount) {
            $this->_ruleFactory->create()->applyAll();
            $this->_backendSession->addWarning(
                __('%1 Catalog Price Rules based on "%2" attribute have been disabled.', $disabledRulesCount, $attributeCode)
            );
        }

        return $this;
    }

    /**
     * Remove catalog attribute condition by attribute code from rule conditions
     *
     * @param \Magento\CatalogRule\Model\Rule\Condition\Combine $combine
     *
     * @param string $attributeCode
     */
    protected function _removeAttributeFromConditions($combine, $attributeCode)
    {
        $conditions = $combine->getConditions();
        foreach ($conditions as $conditionId => $condition) {
            if ($condition instanceof \Magento\CatalogRule\Model\Rule\Condition\Combine) {
                $this->_removeAttributeFromConditions($condition, $attributeCode);
            }
            if ($condition instanceof \Magento\Rule\Model\Condition\Product\AbstractProduct) {
                if ($condition->getAttribute() == $attributeCode) {
                    unset($conditions[$conditionId]);
                }
            }
        }
        $combine->setConditions($conditions);
    }

    /**
     * After save attribute if it is not used for promo rules already check rules for containing this attribute
     *
     * @param \Magento\Event\Observer $observer
     *
     * @return \Magento\CatalogRule\Model\Observer
     */
    public function catalogAttributeSaveAfter(\Magento\Event\Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->dataHasChangedFor('is_used_for_promo_rules') && !$attribute->getIsUsedForPromoRules()) {
            $this->_checkCatalogRulesAvailability($attribute->getAttributeCode());
        }

        return $this;
    }

    /**
     * After delete attribute check rules that contains deleted attribute
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\CatalogRule\Model\Observer
     */
    public function catalogAttributeDeleteAfter(\Magento\Event\Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->getIsUsedForPromoRules()) {
            $this->_checkCatalogRulesAvailability($attribute->getAttributeCode());
        }

        return $this;
    }

    public function prepareCatalogProductCollectionPrices(\Magento\Event\Observer $observer)
    {
        /* @var $collection \Magento\Catalog\Model\Resource\Product\Collection */
        $collection = $observer->getEvent()->getCollection();
        $store      = $this->_storeManager->getStore($observer->getEvent()->getStoreId());
        $websiteId  = $store->getWebsiteId();
        if ($observer->getEvent()->hasCustomerGroupId()) {
            $groupId = $observer->getEvent()->getCustomerGroupId();
        } else {
            if ($this->_customerSession->isLoggedIn()) {
                $groupId = $this->_customerSession->getCustomerGroupId();
            } else {
                $groupId = \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
            }
        }
        if ($observer->getEvent()->hasDate()) {
            $date = $observer->getEvent()->getDate();
        } else {
            $date = $this->_locale->storeTimeStamp($store);
        }

        $productIds = array();
        /* @var $product \Magento\Catalog\Model\Product */
        foreach ($collection as $product) {
            $key = implode('|', array($date, $websiteId, $groupId, $product->getId()));
            if (!isset($this->_rulePrices[$key])) {
                $productIds[] = $product->getId();
            }
        }

        if ($productIds) {
            $rulePrices = $this->_resourceRuleFactory->create()
                ->getRulePrices($date, $websiteId, $groupId, $productIds);
            foreach ($productIds as $productId) {
                $key = implode('|', array($date, $websiteId, $groupId, $productId));
                $this->_rulePrices[$key] = isset($rulePrices[$productId]) ? $rulePrices[$productId] : false;
            }
        }

        return $this;
    }

    /**
     * Create catalog rule relations for imported products
     *
     * @param \Magento\Event\Observer $observer
     */
    public function createCatalogRulesRelations(\Magento\Event\Observer $observer)
    {
        $adapter = $observer->getEvent()->getAdapter();
        $affectedEntityIds = $adapter->getAffectedEntityIds();

        if (empty($affectedEntityIds)) {
            return;
        }

        $rules = $this->_ruleCollFactory->create()
            ->addFieldToFilter('is_active', 1);

        foreach ($rules as $rule) {
            $rule->setProductsFilter($affectedEntityIds);
            $this->_resourceRule->updateRuleProductData($rule);
        }
    }
}
