<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Website;

/**
 * Website Currency Symbol provider
 */
class CurrencySymbolProvider
{
    /**
     * Scope Config Details
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Store Information
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Store locator
     *
     * @var LocatorInterface
     */
    private $locator;

    /**
     * Locale Currency
     *
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * Initialize objects for website currency scope
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LocatorInterface $locator
     * @param CurrencyInterface $localeCurrency
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LocatorInterface $locator,
        CurrencyInterface $localeCurrency
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->locator = $locator;
        $this->localeCurrency = $localeCurrency;
    }

    /**
     * Get option array of currency symbol prefixes.
     *
     * @return array
     */
    public function getCurrenciesPerWebsite(): array
    {
        $baseCurrency = $this->locator->getStore()
            ->getBaseCurrency();
        $websitesCurrencySymbol[0] = $baseCurrency->getCurrencySymbol() ??
            $baseCurrency->getCurrencyCode();
        $catalogPriceScope = $this->getCatalogPriceScope();
        $product = $this->locator->getProduct();
        $websitesList = $this->storeManager->getWebsites();
        $productWebsiteIds = $product->getWebsiteIds();
        if ($catalogPriceScope!=0) {
            foreach ($websitesList as $website) {
                /** @var Website $website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $websitesCurrencySymbol[$website->getId()] = $this
                    ->getCurrencySymbol(
                        $website->getBaseCurrencyCode()
                    );
            }
        }
        return $websitesCurrencySymbol;
    }

    /**
     * Get default store currency symbol
     *
     * @return string
     */
    public function getDefaultCurrency(): string
    {
        $baseCurrency = $this->locator->getStore()
            ->getBaseCurrency();
        return $baseCurrency->getCurrencySymbol() ??
            $baseCurrency->getCurrencyCode();
    }

    /**
     * Get catalog price scope from the admin config
     *
     * @return int
     */
    public function getCatalogPriceScope(): int
    {
        return (int) $this->scopeConfig->getValue(
            Store::XML_PATH_PRICE_SCOPE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Retrieve currency name by code
     *
     * @param   string $code
     * @return  string
     */
    private function getCurrencySymbol(string $code): string
    {
        $currency = $this->localeCurrency->getCurrency($code);
        return $currency->getSymbol() ?
            $currency->getSymbol() : $currency->getShortName();
    }
}
