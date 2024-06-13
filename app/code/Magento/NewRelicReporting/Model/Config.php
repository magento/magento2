<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\NewRelicReporting\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * NewRelic configuration model
 */
class Config
{
    /**#@+
     * Names of parameters to be sent to database tables
     */
    public const ORDER_ITEMS = 'lineItemCount';
    public const ORDER_VALUE = 'orderValue';
    public const ORDER_PLACED = 'Order';
    public const ADMIN_USER_ID = 'adminId';
    public const ADMIN_USER = 'adminUser';
    public const ADMIN_NAME = 'adminName';
    public const CUSTOMER_ID = 'customerId';
    public const CUSTOMER_NAME = 'CustomerName';
    public const CUSTOMER_COUNT = 'CustomerCount';
    public const FLUSH_CACHE = 'systemCacheFlush';
    public const STORE = 'store';
    public const STORE_VIEW_COUNT = 'StoreViewCount';
    public const WEBSITE = 'website';
    public const WEBSITE_COUNT = 'WebsiteCount';
    public const PRODUCT_CHANGE = 'adminProductChange';
    public const PRODUCT_COUNT = 'productCatalogSize';
    public const CONFIGURABLE_COUNT = 'productCatalogConfigurableSize';
    public const ACTIVE_COUNT = 'productCatalogActiveSize';
    public const CATEGORY_SIZE = 'productCatalogCategorySize';
    public const CATEGORY_COUNT = 'CatalogCategoryCount';
    public const ENABLED_MODULE_COUNT = 'enabledModuleCount';
    public const MODULES_ENABLED = 'ModulesEnabled';
    public const MODULES_DISABLED = 'ModulesDisabled';
    public const MODULES_INSTALLED = 'ModulesInstalled';
    public const MODULE_INSTALLED = 'moduleInstalled';
    public const MODULE_UNINSTALLED = 'moduleUninstalled';
    public const MODULE_ENABLED = 'moduleEnabled';
    public const MODULE_DISABLED = 'moduleDisabled';
    /**#@-*/

    /**#@+
     * Text flags for states
     */
    public const INSTALLED = 'installed';
    public const UNINSTALLED = 'uninstalled';
    public const ENABLED = 'enabled';
    public const DISABLED = 'disabled';
    public const TRUE = 'true';
    public const FALSE = 'false';
    /**#@-*/

    /**#@+
     * Configuration paths
     */
    public const XML_PATH_ENABLED = 'newrelicreporting/general/enable';
    public const XML_PATH_API_URL = 'newrelicreporting/general/api_url';
    public const XML_PATH_INSIGHTS_API_URL = 'newrelicreporting/general/insights_api_url';
    public const XML_PATH_ACCOUNT_ID = 'newrelicreporting/general/account_id';
    public const XML_PATH_APP_ID = 'newrelicreporting/general/app_id';
    public const XML_PATH_API_KEY = 'newrelicreporting/general/api';
    public const XML_PATH_INSIGHTS_INSERT_KEY = 'newrelicreporting/general/insights_insert_key';
    public const XML_PATH_APP_NAME = 'newrelicreporting/general/app_name';
    public const XML_PATH_SEPARATE_APPS = 'newrelicreporting/general/separate_apps';
    public const XML_PATH_CRON_ENABLED = 'newrelicreporting/cron/enable_cron';
    public const XML_PATH_API_MODE = 'newrelicreporting/general/api_mode';
    public const XML_PATH_ENTITY_GUID = 'newrelicreporting/general/entity_guid';
    public const XML_PATH_NERD_GRAPH_API_URL = 'newrelicreporting/general/nerd_graph_api_url';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * Returns module's enabled status
     *
     * @return bool
     */
    public function isNewRelicEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Returns configured URL for API
     *
     * @return string
     */
    public function getNewRelicApiUrl()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_API_URL);
    }

    /**
     * Returns configured URL for Insights API
     *
     * @return string
     */
    public function getInsightsApiUrl()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_INSIGHTS_API_URL);
    }

    /**
     * Returns configured account ID for New Relic
     *
     * @return string
     */
    public function getNewRelicAccountId()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ACCOUNT_ID);
    }

    /**
     * Return configured NR Application ID
     *
     * @return int
     */
    public function getNewRelicAppId()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_APP_ID);
    }

    /**
     * Returns configured API key for APM
     *
     * @return string
     */
    public function getNewRelicApiKey()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XML_PATH_API_KEY));
    }

    /**
     * Returns configured Insights insert key for New Relic events related to cron jobs
     *
     * @return string
     */
    public function getInsightsInsertKey()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XML_PATH_INSIGHTS_INSERT_KEY));
    }

    /**
     * Returns configured NR Application name
     *
     * @return string
     */
    public function getNewRelicAppName()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_APP_NAME);
    }

    /**
     * Returns configured separate apps value
     *
     * @return bool
     */
    public function isSeparateApps()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SEPARATE_APPS);
    }

    /**
     * Returns config setting for overall cron to be enabled
     *
     * @return bool
     */
    public function isCronEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CRON_ENABLED);
    }

    /**
     * Sets config value
     *
     * @param string $pathId
     * @param mixed $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    protected function setConfigValue($pathId, $value, $scope = 'default', $scopeId = 0)
    {
        $this->resourceConfig->saveConfig($pathId, $value, $scope, $scopeId);
    }

    /**
     * Disable module's functionality for case when new relic extension is not available
     *
     * @return void
     */
    public function disableModule()
    {
        $this->setConfigValue(self::XML_PATH_ENABLED, 0);
    }

    /**
     * Returns configured API mode for deployments
     *
     * @return string
     */
    public function getApiMode()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_API_MODE);
    }

    /**
     * Returns configured Entity GUID for NerdGraph
     *
     * @return string
     */
    public function getEntityGuid()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ENTITY_GUID);
    }

    /**
     * Check if current configuration is using NerdGraph mode
     *
     * @return bool
     */
    public function isNerdGraphMode()
    {
        return $this->getApiMode() === 'nerdgraph';
    }

    /**
     * Get NerdGraph API URL
     *
     * @return string
     */
    public function getNerdGraphUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_NERD_GRAPH_API_URL);
    }
}
