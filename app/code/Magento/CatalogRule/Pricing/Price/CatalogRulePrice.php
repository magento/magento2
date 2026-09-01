<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CatalogRulePrice extends AbstractPrice implements BasePriceProviderInterface
{
    /**
     * Price type identifier string
     */
    public const PRICE_CODE = 'catalog_rule_price';

    /**
     * @var TimezoneInterface
     */
    protected $dateTime;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Rule
     */
    private $ruleResource;

    /**
     * @var RulePricesStorage
     */
    private $rulePricesStorage;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param Calculator $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $dateTime
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param Rule $ruleResource
     * @param RulePricesStorage|null $rulePricesStorage
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        Calculator $calculator,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $dateTime,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        Rule $ruleResource,
        ?RulePricesStorage $rulePricesStorage = null
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->ruleResource = $ruleResource;
        $this->rulePricesStorage = $rulePricesStorage
            ?: ObjectManager::getInstance()->get(RulePricesStorage::class);
    }

    /**
     * Returns catalog rule value
     *
     * @return float|boolean
     */
    public function getValue()
    {
        if (null === $this->value) {
            if ($this->product->hasData(self::PRICE_CODE)) {
                $value = $this->product->getData(self::PRICE_CODE);
                $this->value = $value ? (float)$value : false;
            } else {
                $date = $this->dateTime->scopeDate($this->storeManager->getStore()->getId());
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                $customerGroupId = $this->customerSession->getCustomerGroupId();
                $productId = $this->product->getId();

                // Catalog rule prices for a whole product collection are fetched in one
                // query by PrepareCatalogProductCollectionPricesObserver and kept in
                // RulePricesStorage. Reuse that result when it is present instead of
                // issuing a single-row query for every product being rendered.
                $key = "{$date->format('Y-m-d H:i:s')}|{$websiteId}|{$customerGroupId}|{$productId}";
                if ($this->rulePricesStorage->hasRulePrice($key)) {
                    $this->value = $this->rulePricesStorage->getRulePrice($key);
                } else {
                    $this->value = $this->ruleResource->getRulePrice(
                        $date,
                        $websiteId,
                        $customerGroupId,
                        $productId
                    );
                    $this->rulePricesStorage->setRulePrice($key, $this->value);
                }
                $this->value = $this->value ? (float)$this->value : false;
            }
            if ($this->value) {
                $this->value = $this->priceCurrency->convertAndRound($this->value, null, null, 4);
            }
        }

        return $this->value;
    }
}
