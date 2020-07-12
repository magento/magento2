<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Builds ProductTierPriceInterface objects
 */
class TierPriceBuilder
{
    /**
     * @var int
     */
    private $websiteId;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    private $tierPriceFactory;

    /**
     * @var ProductTierPriceExtensionFactory
     */
    private $tierPriceExtensionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ProductTierPriceExtensionFactory $tierPriceExtensionFactory
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->tierPriceFactory = $tierPriceFactory;
        $this->tierPriceExtensionFactory = $tierPriceExtensionFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;

        $this->setWebsiteId();
    }

    /**
     * Gets list of product tier prices
     *
     * @param ProductInterface $product
     * @return ProductTierPriceInterface[]
     */
    public function getTierPrices($product)
    {
        /** @var array $tierPricesRaw */
        $tierPricesRaw = $this->loadData($product);

        return $this->buildTierPriceObjects($tierPricesRaw);
    }

    /**
     * Get tier data for a product
     *
     * @param ProductInterface $product
     * @return array
     */
    private function loadData(ProductInterface $product): array
    {
        $tierData = $product->getData(ProductAttributeInterface::CODE_TIER_PRICE);

        if ($tierData === null) {
            $attribute = $product->getResource()->getAttribute(ProductAttributeInterface::CODE_TIER_PRICE);
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $tierData = $product->getData(ProductAttributeInterface::CODE_TIER_PRICE);
            }
        }

        if ($tierData === null || !is_array($tierData)) {
            return [];
        }

        return $tierData;
    }

    /**
     * Transform the raw tier data into array of ProductTierPriceInterface objects
     *
     * @param array $tierPricesRaw
     * @return ProductTierPriceInterface[]
     */
    private function buildTierPriceObjects(array $tierPricesRaw): array
    {
        $prices = [];

        foreach ($tierPricesRaw as $tierPriceRaw) {
            $prices[] = $this->createTierPriceObjectFromRawData($tierPriceRaw);
        }

        return $prices;
    }

    /**
     * Transform the raw tier price data into ProductTierPriceInterface object
     *
     * @param array $tierPriceRaw
     * @return ProductTierPriceInterface
     */
    private function createTierPriceObjectFromRawData(array $tierPriceRaw): ProductTierPriceInterface
    {
        /** @var ProductTierPriceInterface $tierPrice */
        $tierPrice = $this->tierPriceFactory->create()
            ->setExtensionAttributes($this->tierPriceExtensionFactory->create());

        $tierPrice->setCustomerGroupId(
            isset($tierPriceRaw['cust_group']) ? $tierPriceRaw['cust_group'] : ''
        );
        $tierPrice->setValue(
            $this->getPriceValue($tierPriceRaw)
        );
        $tierPrice->setQty(
            isset($tierPriceRaw['price_qty']) ? $tierPriceRaw['price_qty'] : ''
        );
        $tierPrice->getExtensionAttributes()->setWebsiteId(
            isset($tierPriceRaw['website_id']) ? $tierPriceRaw['website_id'] : $this->websiteId
        );
        if (isset($tierPriceRaw['percentage_value'])) {
            $tierPrice->getExtensionAttributes()->setPercentageValue(
                $tierPriceRaw['percentage_value']
            );
        }

        return $tierPrice;
    }

    /**
     * Get price value
     *
     * @param array $tierPriceRaw
     * @return float
     */
    private function getPriceValue(array $tierPriceRaw): float
    {
        $valueInDefaultCurrency = $this->extractPriceValue($tierPriceRaw);
        $valueInStoreCurrency = $this->priceCurrency->convertAndRound($valueInDefaultCurrency);

        return $valueInStoreCurrency;
    }

    /**
     * Extract float price value from raw data
     *
     * @param array $tierPriceRaw
     * @return float
     */
    private function extractPriceValue(array $tierPriceRaw): float
    {
        if (isset($tierPriceRaw['website_price'])) {
            return (float)$tierPriceRaw['website_price'];
        }

        return (float)$tierPriceRaw['price'];
    }

    /**
     * Find and set the website id
     */
    private function setWebsiteId()
    {
        $websiteId = 0;
        $value = $this->config->getValue('catalog/price/scope', ScopeInterface::SCOPE_WEBSITE);
        if ($value != 0) {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }

        $this->websiteId = $websiteId;
    }
}
