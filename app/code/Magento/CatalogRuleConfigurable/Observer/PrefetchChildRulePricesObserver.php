<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Observer;

use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Prefetch catalog rule prices for the child products of the configurable products in a collection.
 *
 * The price displayed for a configurable product is resolved from one of its child products, and
 * those children are not members of the collection being prepared, so the prefetch performed by
 * Magento\CatalogRule\Observer\PrepareCatalogProductCollectionPricesObserver does not cover them.
 * Each rendered configurable therefore produced its own single row catalogrule_product_price query.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrefetchChildRulePricesObserver implements ObserverInterface
{
    /**
     * @var RulePricesStorage
     */
    private $rulePricesStorage;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var ConfigurableResource
     */
    private $configurableResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param RulePricesStorage $rulePricesStorage
     * @param RuleFactory $ruleFactory
     * @param ConfigurableResource $configurableResource
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     */
    public function __construct(
        RulePricesStorage $rulePricesStorage,
        RuleFactory $ruleFactory,
        ConfigurableResource $configurableResource,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        Session $customerSession
    ) {
        $this->rulePricesStorage = $rulePricesStorage;
        $this->ruleFactory = $ruleFactory;
        $this->configurableResource = $configurableResource;
        $this->storeManager = $storeManager;
        $this->localeDate = $localeDate;
        $this->customerSession = $customerSession;
    }

    /**
     * Load the catalog rule prices of the collection's configurable children in one query.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $parentIds = [];
        foreach ($observer->getEvent()->getCollection() as $product) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $parentIds[] = (int)$product->getId();
            }
        }

        if (!$parentIds) {
            return;
        }

        $childIds = $this->getChildIds($parentIds);
        if (!$childIds) {
            return;
        }

        $event = $observer->getEvent();
        $store = $this->storeManager->getStore($event->getStoreId());
        $websiteId = $store->getWebsiteId();
        $customerGroupId = $event->hasCustomerGroupId()
            ? $event->getCustomerGroupId()
            : $this->customerSession->getCustomerGroupId();
        $date = $event->hasDate()
            ? new \DateTime($event->getDate())
            : $this->localeDate->scopeDate($store->getId());
        $dateKey = $date->format('Y-m-d H:i:s');

        $missing = [];
        foreach ($childIds as $childId) {
            if (!$this->rulePricesStorage->hasRulePrice("{$dateKey}|{$websiteId}|{$customerGroupId}|{$childId}")) {
                $missing[] = $childId;
            }
        }

        if (!$missing) {
            return;
        }

        $prices = $this->ruleFactory->create()->getRulePrices($date, $websiteId, $customerGroupId, $missing);
        foreach ($missing as $childId) {
            $this->rulePricesStorage->setRulePrice(
                "{$dateKey}|{$websiteId}|{$customerGroupId}|{$childId}",
                $prices[$childId] ?? false
            );
        }
    }

    /**
     * Child product ids of the given configurable products.
     *
     * The configurable resource resolves the parents through the product link field, so the
     * lookup is right whether catalog_product_super_link.parent_id holds entity_id or row_id.
     *
     * @param int[] $parentIds
     * @return int[]
     */
    private function getChildIds(array $parentIds): array
    {
        $childIds = $this->configurableResource->getChildrenIds($parentIds)[0] ?? [];

        return array_map('intval', array_values($childIds));
    }
}
