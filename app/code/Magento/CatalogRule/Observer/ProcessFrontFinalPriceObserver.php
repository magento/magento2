<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;

/**
 * Observer for applying catalog rules on product for frontend area
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ProcessFrontFinalPriceObserver implements ObserverInterface
{
    /**
     * @var CustomerModelSession
     */
    protected $customerSession;

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
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerModelSession $customerSession
     */
    public function __construct(
        RulePricesStorage $rulePricesStorage,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerModelSession $customerSession
    ) {
        $this->rulePricesStorage = $rulePricesStorage;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->customerSession = $customerSession;
    }

    /**
     * Apply catalog price rules to product on frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $pId = $product->getId();
        $storeId = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = new \DateTime($observer->getEvent()->getDate());
        } else {
            $date = $this->localeDate->scopeDate($storeId);
        }

        if ($observer->hasWebsiteId()) {
            $wId = $observer->getEvent()->getWebsiteId();
        } else {
            $wId = $this->storeManager->getStore($storeId)->getWebsiteId();
        }

        if ($observer->hasCustomerGroupId()) {
            $gId = $observer->getEvent()->getCustomerGroupId();
        } elseif ($product->hasCustomerGroupId()) {
            $gId = $product->getCustomerGroupId();
        } else {
            $gId = $this->customerSession->getCustomerGroupId();
        }

        $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}";
        if (!$this->rulePricesStorage->hasRulePrice($key)) {
            $rulePrice = $this->resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId);
            $this->rulePricesStorage->setRulePrice($key, $rulePrice);
        }
        if ($this->rulePricesStorage->getRulePrice($key) !== false) {
            $finalPrice = min($product->getData('final_price'), $this->rulePricesStorage->getRulePrice($key));
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }
}
