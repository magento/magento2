<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleGraphQl\Plugin\Pricing\Price;

use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class UpdateCatalogRulePrice
 *
 * Plugin to update catalog rule price based on customer group id
 */
class UpdateCatalogRulePrice
{
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @var Rule
     */
    private $ruleResource;

    /**
     * @var RulePricesStorage
     */
    private $rulePricesStorage;

    /**
     * @param TimezoneInterface $dateTime
     * @param Rule $ruleResource
     * @param RulePricesStorage|null $rulePricesStorage
     */
    public function __construct(
        TimezoneInterface $dateTime,
        Rule $ruleResource,
        ?RulePricesStorage $rulePricesStorage = null
    ) {
        $this->dateTime = $dateTime;
        $this->ruleResource = $ruleResource;
        $this->rulePricesStorage = $rulePricesStorage
            ?? ObjectManager::getInstance()->get(RulePricesStorage::class);
    }

    /**
     * Returns catalog rule value for logged in customer group
     *
     * Uses the price prefetched for the whole product collection by the
     * prepare_catalog_product_collection_prices event when it is in storage.
     *
     * @param CatalogRulePrice $catalogRulePrice
     * @param float|boolean $value
     * @return float|boolean
     */
    public function afterGetValue(
        CatalogRulePrice $catalogRulePrice,
        $value
    ) {
        $product = $catalogRulePrice->getProduct();
        if ($product && $product->getCustomerGroupId()) {
            $store = $product->getStore();
            $date = $this->dateTime->scopeDate($store->getId());
            $websiteId = $store->getWebsiteId();
            $customerGroupId = $product->getCustomerGroupId();
            $productId = $product->getId();
            $key = "{$date->format('Y-m-d H:i:s')}|{$websiteId}|{$customerGroupId}|{$productId}";
            $value = $this->rulePricesStorage->hasRulePrice($key)
                ? $this->rulePricesStorage->getRulePrice($key)
                : $this->ruleResource->getRulePrice($date, $websiteId, $customerGroupId, $productId);
            $value = $value ? (float) $value : false;
        }

        return $value;
    }
}
