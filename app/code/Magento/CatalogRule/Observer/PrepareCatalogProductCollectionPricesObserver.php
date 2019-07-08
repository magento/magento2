<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Price rules observer model
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerModelSession;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Observer for applying catalog rules on product collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class PrepareCatalogProductCollectionPricesObserver implements ObserverInterface
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
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var \Magento\CatalogRule\Model\RuleDateFormatterInterface
     */
    private $ruleDateFormatter;

    /**
     * @param RulePricesStorage $rulePricesStorage
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param CustomerModelSession $customerSession
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\CatalogRule\Model\RuleDateFormatterInterface|null $ruleDateFormatter
     */
    public function __construct(
        RulePricesStorage $rulePricesStorage,
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $resourceRuleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        CustomerModelSession $customerSession,
        GroupManagementInterface $groupManagement,
        \Magento\CatalogRule\Model\RuleDateFormatterInterface $ruleDateFormatter = null
    ) {
        $this->rulePricesStorage = $rulePricesStorage;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->customerSession = $customerSession;
        $this->groupManagement = $groupManagement;
        $this->ruleDateFormatter = $ruleDateFormatter ?: ObjectManager::getInstance()
            ->get(\Magento\CatalogRule\Model\RuleDateFormatterInterface::class);
    }

    /**
     * Apply catalog price rules to product on frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $collection ProductCollection */
        $collection = $observer->getEvent()->getCollection();
        $store = $this->storeManager->getStore($observer->getEvent()->getStoreId());
        $websiteId = $store->getWebsiteId();
        if ($observer->getEvent()->hasCustomerGroupId()) {
            $groupId = $observer->getEvent()->getCustomerGroupId();
        } else {
            if ($this->customerSession->isLoggedIn()) {
                $groupId = $this->customerSession->getCustomerGroupId();
            } else {
                $groupId = $this->groupManagement->getNotLoggedInGroup()->getId();
            }
        }
        if ($observer->getEvent()->hasDate()) {
            $date = new \DateTime($observer->getEvent()->getDate());
        } else {
            $date = (new \DateTime())->setTimestamp($this->ruleDateFormatter->getTimeStamp($store));
        }

        $productIds = [];
        /* @var $product Product */
        foreach ($collection as $product) {
            $key = implode('|', [$date->format('Y-m-d H:i:s'), $websiteId, $groupId, $product->getId()]);
            if (!$this->rulePricesStorage->hasRulePrice($key)) {
                $productIds[] = $product->getId();
            }
        }

        if ($productIds) {
            $rulePrices = $this->resourceRuleFactory->create()->getRulePrices(
                $date,
                $websiteId,
                $groupId,
                $productIds
            );
            foreach ($productIds as $productId) {
                $key = implode('|', [$date->format('Y-m-d H:i:s'), $websiteId, $groupId, $productId]);
                $this->rulePricesStorage->setRulePrice(
                    $key,
                    isset($rulePrices[$productId]) ? $rulePrices[$productId] : false
                );
            }
        }

        return $this;
    }
}
