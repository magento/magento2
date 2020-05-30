<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Observer for applying catalog rules on product for admin area
 */
class ProcessAdminFinalPriceObserver implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory
     */
    protected $resourceRuleFactory;

    /**
     * @var \Magento\CatalogRule\Observer\RulePricesStorage
     */
    protected $rulePricesStorage;

    /**
     * @param RulePricesStorage $rulePricesStorage
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        RulePricesStorage $rulePricesStorage,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory,
        TimezoneInterface $localeDate
    ) {
        $this->rulePricesStorage = $rulePricesStorage;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->localeDate = $localeDate;
    }

    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
        } elseif ($product->getWebsiteId() === null && $product->getCustomerGroupId() !== null) {
            $wId = $this->storeManager->getStore($storeId)->getWebsiteId();
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
