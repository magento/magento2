<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
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
     * @var \Magento\CatalogRule\Model\ResourceModel\RuleFactory
     * @deprecated 100.0.2
     */
    protected $resourceRuleFactory;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule
     */
    private $ruleResource;

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
            if ($this->product->hasData(self::PRICE_CODE)) {
                $this->value = (float)$this->product->getData(self::PRICE_CODE) ?: false;
            } else {
                $this->value = $this->getRuleResource()
                    ->getRulePrice(
                        $this->dateTime->scopeDate($this->storeManager->getStore()->getId()),
                        $this->storeManager->getStore()->getWebsiteId(),
                        $this->customerSession->getCustomerGroupId(),
                        $this->product->getId()
                    );
                $this->value = $this->value ? (float)$this->value : false;
            }
            if ($this->value) {
                $this->value = $this->priceCurrency->convertAndRound($this->value);
            }
        }

        return $this->value;
    }

    /**
     * @return Rule
     * @deprecated 100.1.1
     */
    private function getRuleResource()
    {
        if (null === $this->ruleResource) {
            $this->ruleResource = ObjectManager::getInstance()->get(Rule::class);
        }

        return $this->ruleResource;
    }
}
