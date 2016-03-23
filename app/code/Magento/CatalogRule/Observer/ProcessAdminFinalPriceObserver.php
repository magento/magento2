<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Price rules observer model
 */
namespace Magento\CatalogRule\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;

class ProcessAdminFinalPriceObserver implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory
     */
    protected $resourceRuleFactory;

    /** @var RulePricesStorage  */
    protected $rulePricesStorage;

    /**
     * @param RulePricesStorage $rulePricesStorage
     * @param Registry $coreRegistry
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        RulePricesStorage $rulePricesStorage,
        Registry $coreRegistry,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory,
        TimezoneInterface $localeDate
    ) {
        $this->rulePricesStorage = $rulePricesStorage;
        $this->coreRegistry = $coreRegistry;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->localeDate = $localeDate;
    }

    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeId = $product->getStoreId();
        $date = $this->localeDate->scopeDate($storeId);
        $key = false;

        $ruleData = $this->coreRegistry->registry('rule_data');
        if ($ruleData) {
            $wId = $ruleData->getWebsiteId();
            $gId = $ruleData->getCustomerGroupId();
            $pId = $product->getId();

            $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}";
        } elseif ($product->getWebsiteId() !== null && $product->getCustomerGroupId() !== null) {
            $wId = $product->getWebsiteId();
            $gId = $product->getCustomerGroupId();
            $pId = $product->getId();
            $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}";
        }

        if ($key) {
            if (!$this->rulePricesStorage->hasRulePrice($key)) {
                $rulePrice = $this->resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId);
                $this->rulePricesStorage->setRulePrice($key, $rulePrice);
            }
            if ($this->rulePricesStorage->getRulePrice($key) !== false) {
                $finalPrice = min($product->getData('final_price'), $this->rulePricesStorage->getRulePrice($key));
                $product->setFinalPrice($finalPrice);
            }
        }

        return $this;
    }
}
