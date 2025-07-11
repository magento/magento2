<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Model\System;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Custom currency symbol model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Currencysymbol
{
    /**
     * Custom currency symbol properties
     *
     * @var array
     */
    protected $_symbolsData = [];

    /**
     * Current store id
     *
     * @var string|null
     */
    protected $_storeId;

    /**
     * Current website id
     *
     * @var string|null
     */
    protected $_websiteId;

    /**
     * Cache types which should be invalidated
     *
     * @var array
     */
    protected $_cacheTypes = [
        \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
        \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER,
        \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER,
        \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER,
    ];

    /**
     * Config path to custom currency symbol value
     */
    public const XML_PATH_CUSTOM_CURRENCY_SYMBOL = 'currency/options/customsymbol';

    public const XML_PATH_ALLOWED_CURRENCIES = \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW;

    /*
     * Separator used in config in allowed currencies list
     */
    public const ALLOWED_CURRENCIES_CONFIG_SEPARATOR = ',';

    /**
     * Config currency section
     */
    public const CONFIG_SECTION = 'currency';

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    protected $_coreConfig;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig,
        \Magento\Config\Model\Config\Factory $configFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        ?Json $serializer = null
    ) {
        $this->_coreConfig = $coreConfig;
        $this->_configFactory = $configFactory;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->_systemStore = $systemStore;
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Return currency symbol properties array based on config values
     *
     * @return array
     */
    public function getCurrencySymbolsData()
    {
        if ($this->_symbolsData) {
            return $this->_symbolsData;
        }

        $this->_symbolsData = [];

        $currentSymbols = $this->_unserializeStoreConfig(self::XML_PATH_CUSTOM_CURRENCY_SYMBOL);

        foreach ($this->getAllowedCurrencies() as $code) {
            $currencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'];
            $symbol = $currencies[$code][0] ?: $code;
            $name = $currencies[$code][1] ?: $code;
            $this->_symbolsData[$code] = ['parentSymbol' => $symbol, 'displayName' => $name];

            if (isset($currentSymbols[$code]) && !empty($currentSymbols[$code])) {
                $this->_symbolsData[$code]['displaySymbol'] = $currentSymbols[$code];
                $this->_symbolsData[$code]['inherited'] = false;
            } else {
                $this->_symbolsData[$code]['displaySymbol'] = $this->_symbolsData[$code]['parentSymbol'];
                $this->_symbolsData[$code]['inherited'] = true;
            }
        }

        return $this->_symbolsData;
    }

    /**
     * Save currency symbol to config
     *
     * @param array $symbols
     * @return $this
     */
    public function setCurrencySymbolsData($symbols = [])
    {
        if (!$this->_storeManager->isSingleStoreMode()) {
            foreach (array_keys($this->getCurrencySymbolsData()) as $code) {
                if (isset($symbols[$code]) && empty($symbols[$code])) {
                    unset($symbols[$code]);
                }
            }
        }
        $value = [];
        if ($symbols) {
            $value['options']['fields']['customsymbol']['value'] = $this->serializer->serialize($symbols);
        } else {
            $value['options']['fields']['customsymbol']['inherit'] = 1;
        }

        $this->_configFactory->create()
            ->setSection(self::CONFIG_SECTION)
            ->setWebsite(null)
            ->setStore(null)
            ->setGroups($value)
            ->save();

        $this->_eventManager->dispatch(
            'admin_system_config_changed_section_currency_before_reinit',
            ['website' => $this->_websiteId, 'store' => $this->_storeId]
        );

        // reinit configuration
        $this->_coreConfig->reinit();

        $this->clearCache();
        //Reset symbols cache since new data is added
        $this->_symbolsData = [];

        $this->_eventManager->dispatch(
            'admin_system_config_changed_section_currency',
            ['website' => $this->_websiteId, 'store' => $this->_storeId]
        );

        return $this;
    }

    /**
     * Return custom currency symbol by currency code
     *
     * @param string $code
     * @return string|false
     */
    public function getCurrencySymbol($code)
    {
        $customSymbols = $this->_unserializeStoreConfig(self::XML_PATH_CUSTOM_CURRENCY_SYMBOL);
        if (array_key_exists($code, $customSymbols)) {
            return $customSymbols[$code];
        }

        return false;
    }

    /**
     * Clear translate cache
     *
     * @return $this
     */
    protected function clearCache()
    {
        // clear cache for frontend
        foreach ($this->_cacheTypes as $cacheType) {
            $this->_cacheTypeList->invalidate($cacheType);
        }
        return $this;
    }

    /**
     * Unserialize data from Store Config.
     *
     * @param string $configPath
     * @param int $storeId
     * @return array
     */
    protected function _unserializeStoreConfig($configPath, $storeId = null)
    {
        $result = [];
        $configData = (string)$this->_scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($configData) {
            $result = $this->serializer->unserialize($configData);
        }

        return is_array($result) ? $result : [];
    }

    /**
     * Return allowed currencies
     *
     * @return array
     */
    protected function getAllowedCurrencies()
    {
        $allowedCurrencies = [];
        $allowedCurrencies[] = explode(
            self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
            $this->_scopeConfig->getValue(
                self::XML_PATH_ALLOWED_CURRENCIES,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
        );

        $storeModel = $this->_systemStore;
        /** @var \Magento\Store\Model\Website $website */
        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            /** @var \Magento\Store\Model\Group $group */
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                /** @var \Magento\Store\Model\Store $store */
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $websiteSymbols = $website->getConfig(self::XML_PATH_ALLOWED_CURRENCIES);
                        $allowedCurrencies[] = explode(self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR, $websiteSymbols);
                    }
                    $storeSymbols = $this->_scopeConfig->getValue(
                        self::XML_PATH_ALLOWED_CURRENCIES,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    $allowedCurrencies[] = explode(self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR, $storeSymbols);
                }
            }
        }
        return array_unique(array_merge([], ...$allowedCurrencies));
    }
}
