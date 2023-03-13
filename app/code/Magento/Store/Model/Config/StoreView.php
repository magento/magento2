<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Retrieves theme and locale info associated with store-views
 */
class StoreView
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ThemeProviderInterface $themeProvider
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly ThemeProviderInterface $themeProvider
    ) {
    }

    /**
     * Retrieves a unique list of pairs representing the theme/locale for each store view
     *
     * @return array
     */
    public function retrieveThemeLocalePairs()
    {
        $stores = $this->storeManager->getStores();
        $localeThemeData = [];

        /** @var StoreInterface $store */
        foreach ($stores as $store) {
            $code = $store->getCode();
            $themeId = $this->scopeConfig->getValue(
                DesignInterface::XML_PATH_THEME_ID,
                ScopeInterface::SCOPE_STORE,
                $code
            );
            $localeThemeData[] = [
                'theme' => $this->themeProvider->getThemeById($themeId)->getCode(),
                'locale' => $this->scopeConfig->getValue(
                    Data::XML_PATH_DEFAULT_LOCALE,
                    ScopeInterface::SCOPE_STORE,
                    $code
                )
            ];
        }

        return $this->removeDuplicates($localeThemeData);
    }

    /**
     * Retrieves a unique list of locales that are used by store views
     *
     * @return array
     */
    public function retrieveLocales()
    {
        $stores = $this->storeManager->getStores();
        $locales = [];

        /** @var StoreInterface $store */
        foreach ($stores as $store) {
            $locales[] = $this->scopeConfig->getValue(
                Data::XML_PATH_DEFAULT_LOCALE,
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
        }

        return $this->removeDuplicates($locales);
    }

    /**
     * Remove duplicate entries in an array
     *
     * @param array $arr
     * @return array
     */
    private function removeDuplicates($arr)
    {
        $len = count($arr);
        for ($out = 0; $out < $len; $out++) {
            $outVal = $arr[$out];
            for ($in = $out + 1; $in < $len; $in++) {
                $inVal = $arr[$in];
                if ($outVal === $inVal) {
                    unset($arr[$out]);
                }
            }
        }
        return $arr;
    }
}
