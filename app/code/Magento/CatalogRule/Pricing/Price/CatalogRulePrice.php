<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Resource\RuleFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManager;

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
     * @var \Magento\CatalogRule\Model\Resource\RuleFactory
     */
    protected $resourceRuleFactory;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param Calculator $calculator
     * @param RuleFactory $catalogRuleResourceFactory
     * @param TimezoneInterface $dateTime
     * @param StoreManager $storeManager
     * @param Session $customerSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
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
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->resourceRuleFactory = $catalogRuleResourceFactory;
    }

    /**
     * Returns catalog rule value
     *
     * @return float|boolean
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = $this->resourceRuleFactory->create()
                ->getRulePrice(
                    $this->dateTime->scopeTimeStamp($this->storeManager->getStore()->getId()),
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
}
