<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Price rules observer model
 */
namespace Magento\CatalogRule\Model;

use Magento\Backend\Model\Session as BackendModelSession;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Resource\Product\Collection as ProductCollection;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Product\Price;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session as CustomerModelSession;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Stdlib\DateTime;

class Observer
{
    /**
     * Store calculated catalog rules prices for products
     * Prices collected per website, customer group, date and product
     *
     * @var array
     */
    protected $_rulePrices = [];

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CustomerModelSession
     */
    protected $_customerSession;

    /**
     * @var Price
     */
    protected $_productPrice;

    /**
     * @var \Magento\CatalogRule\Model\Resource\Rule\CollectionFactory
     */
    protected $_ruleCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\CatalogRule\Model\Resource\RuleFactory
     */
    protected $_resourceRuleFactory;

    /**
     * @var \Magento\CatalogRule\Model\Resource\Rule
     */
    protected $_resourceRule;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @param Resource\RuleFactory $resourceRuleFactory
     * @param Resource\Rule $resourceRule
     * @param Resource\Rule\CollectionFactory $ruleCollectionFactory
     * @param Price $productPrice
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerModelSession $customerSession
     * @param Registry $coreRegistry
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Resource\RuleFactory $resourceRuleFactory,
        Resource\Rule $resourceRule,
        Resource\Rule\CollectionFactory $ruleCollectionFactory,
        Rule\Product\Price $productPrice,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerModelSession $customerSession,
        Registry $coreRegistry,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement
    ) {
        $this->_resourceRuleFactory = $resourceRuleFactory;
        $this->_resourceRule = $resourceRule;
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
        $this->_productPrice = $productPrice;
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->dateTime = $dateTime;
        $this->groupManagement = $groupManagement;
    }

    /**
     * Apply catalog price rules to product on frontend
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function processFrontFinalPrice($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $pId = $product->getId();
        $storeId = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = $observer->getEvent()->getDate();
        } else {
            $date = $this->_localeDate->scopeTimeStamp($storeId);
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

        $key = "{$date}|{$wId}|{$gId}|{$pId}";
        if (!isset($this->_rulePrices[$key])) {
            $rulePrice = $this->_resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId);
            $this->_rulePrices[$key] = $rulePrice;
        }
        if ($this->_rulePrices[$key] !== false) {
            $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }

    /**
     * Apply catalog price rules to product in admin
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function processAdminFinalPrice($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeId = $product->getStoreId();
        $date = $this->_localeDate->scopeDate($storeId);
        $key = false;

        $ruleData = $this->_coreRegistry->registry('rule_data');
        if ($ruleData) {
            $wId = $ruleData->getWebsiteId();
            $gId = $ruleData->getCustomerGroupId();
            $pId = $product->getId();

            $key = "{$date}|{$wId}|{$gId}|{$pId}";
        } elseif (!is_null($product->getWebsiteId()) && !is_null($product->getCustomerGroupId())) {
            $wId = $product->getWebsiteId();
            $gId = $product->getCustomerGroupId();
            $pId = $product->getId();
            $key = "{$date}|{$wId}|{$gId}|{$pId}";
        }

        if ($key) {
            if (!isset($this->_rulePrices[$key])) {
                $rulePrice = $this->_resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId);
                $this->_rulePrices[$key] = $rulePrice;
            }
            if ($this->_rulePrices[$key] !== false) {
                $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                $product->setFinalPrice($finalPrice);
            }
        }

        return $this;
    }

    /**
     * Clean out calculated catalog rule prices for products
     *
     * @return void
     */
    public function flushPriceCache()
    {
        $this->_rulePrices = [];
    }

    /**
     * Calculate minimal final price with catalog rule price
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function prepareCatalogProductPriceIndexTable(EventObserver $observer)
    {
        $select = $observer->getEvent()->getSelect();

        $indexTable = $observer->getEvent()->getIndexTable();
        $entityId = $observer->getEvent()->getEntityId();
        $customerGroupId = $observer->getEvent()->getCustomerGroupId();
        $websiteId = $observer->getEvent()->getWebsiteId();
        $websiteDate = $observer->getEvent()->getWebsiteDate();
        $updateFields = $observer->getEvent()->getUpdateFields();

        $this->_productPrice->applyPriceRuleToIndexTable(
            $select,
            $indexTable,
            $entityId,
            $customerGroupId,
            $websiteId,
            $updateFields,
            $websiteDate
        );

        return $this;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function prepareCatalogProductCollectionPrices(EventObserver $observer)
    {
        /* @var $collection ProductCollection */
        $collection = $observer->getEvent()->getCollection();
        $store = $this->_storeManager->getStore($observer->getEvent()->getStoreId());
        $websiteId = $store->getWebsiteId();
        if ($observer->getEvent()->hasCustomerGroupId()) {
            $groupId = $observer->getEvent()->getCustomerGroupId();
        } else {
            if ($this->_customerSession->isLoggedIn()) {
                $groupId = $this->_customerSession->getCustomerGroupId();
            } else {
                $groupId = $this->groupManagement->getNotLoggedInGroup()->getId();
            }
        }
        if ($observer->getEvent()->hasDate()) {
            $date = $observer->getEvent()->getDate();
        } else {
            $date = $this->_localeDate->scopeTimeStamp($store);
        }

        $productIds = [];
        /* @var $product Product */
        foreach ($collection as $product) {
            $key = implode('|', [$date, $websiteId, $groupId, $product->getId()]);
            if (!isset($this->_rulePrices[$key])) {
                $productIds[] = $product->getId();
            }
        }

        if ($productIds) {
            $rulePrices = $this->_resourceRuleFactory->create()->getRulePrices(
                $date,
                $websiteId,
                $groupId,
                $productIds
            );
            foreach ($productIds as $productId) {
                $key = implode('|', [$date, $websiteId, $groupId, $productId]);
                $this->_rulePrices[$key] = isset($rulePrices[$productId]) ? $rulePrices[$productId] : false;
            }
        }

        return $this;
    }
}
