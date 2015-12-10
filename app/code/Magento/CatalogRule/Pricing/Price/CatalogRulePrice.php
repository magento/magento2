<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManager;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;

/**
 * Class CatalogRulePrice
 */
class CatalogRulePrice extends AbstractPrice implements BasePriceProviderInterface
{
    /**
     * Price type identifier string
     */
    const PRICE_CODE = 'catalog_rule_price';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $dateTime;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory
     */
    protected $resourceRuleFactory;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * CatalogRulePrice constructor.
     * @param Product $saleableItem
     * @param float $quantity
     * @param Calculator $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $dateTime
     * @param StoreManager $storeManager
     * @param Session $customerSession
     * @param RuleFactory $catalogRuleResourceFactory
     * @param CollectionFactory $ruleCollection
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        Calculator $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $dateTime,
        StoreManager $storeManager,
        Session $customerSession,
        RuleFactory $catalogRuleResourceFactory
//        CollectionFactory $ruleCollection
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->resourceRuleFactory = $catalogRuleResourceFactory;
//        $this->ruleCollectionFactory = $ruleCollection;
    }

    /**
     * Returns catalog rule value
     *
     * @return float|boolean
     */
    public function getValue()
    {
        if (null === $this->value) {


            // customerGroupId -> get it from preview / hardcode NON LOGGED IN???
            // website id -> GET IT FROM PREVIEW
            // current date -> get from preview
//            $currentProductPrice = $this->product->getPrice(); // TODO make sure this price is correct
//            foreach ($this->getActiveRules($this->storeManager->getStore()->getWebsiteId(), $this->customerSession->getCustomerGroupId()) as $rule) {
//                if ($rule->validate($this->product)) {
//                    $currentProductPrice = $this->calculateRuleProductPrice($rule, $currentProductPrice);
//                    if ($rule->getStopRulesProcessing()) {
//                        break;
//                    }
//                }
//            }
//            return $currentProductPrice;





            $this->value = $this->resourceRuleFactory->create()
                ->getRulePrice(
                    $this->dateTime->scopeDate($this->storeManager->getStore()->getId()),
                    $this->storeManager->getStore()->getWebsiteId(),
                    $this->customerSession->getCustomerGroupId(),
                    $this->product->getId()
                );
            $this->value = $this->value ? floatval($this->value) : false;
            if ($this->value) {
                $this->value = $this->priceCurrency->convertAndRound($this->value);
            }
        }
        return $this->value;
    }

    /**
     * Get active rules
     *
     * @param int $websiteId
     * @param int $customerGroupId
     *
     * @return array
     */
    protected function getActiveRules($websiteId, $customerGroupId)
    {
        // TODO add ordering by Rule Sort order (maybe cache this collection for future use)
        /** @var \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection */
        return $this->ruleCollectionFactory->create()
            ->addWebsiteFilter($websiteId)
            ->addCustomerGroupFilter($customerGroupId)
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }

    /**
     * Calculate rule product price
     *
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param float $currentProductPrice
     * @return float
     */
    protected function calculateRuleProductPrice(\Magento\CatalogRule\Model\Rule $rule, $currentProductPrice)
    {
        switch ($rule->getSimpleAction()) {
            case 'to_fixed':
                $currentProductPrice = min($rule->getDiscountAmount(), $currentProductPrice);
                break;
            case 'to_percent':
                $currentProductPrice = $currentProductPrice * $rule->getDiscountAmount() / 100;
                break;
            case 'by_fixed':
                $currentProductPrice = max(0, $currentProductPrice - $rule->getDiscountAmount());
                break;
            case 'by_percent':
                $currentProductPrice = $currentProductPrice * (1 - $rule->getDiscountAmount() / 100);
                break;
            default:
                $currentProductPrice = 0;
        }

        return $this->priceCurrency->round($currentProductPrice);
    }
}
