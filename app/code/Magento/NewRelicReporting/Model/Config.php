<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

/**
 * Class \Magento\NewRelicReporting\Model\Config
 *
 * @since 2.0.0
 */
class Config
{
    /**#@+
     * Names of parameters to be sent to database tables
     */
    const ORDER_ITEMS = 'lineItemCount';
    const ORDER_VALUE = 'orderValue';
    const ORDER_PLACED = 'Order';
    const ADMIN_USER_ID = 'adminId';
    const ADMIN_USER = 'adminUser';
    const ADMIN_NAME = 'adminName';
    const CUSTOMER_ID = 'customerId';
    const CUSTOMER_NAME = 'CustomerName';
    const CUSTOMER_COUNT = 'CustomerCount';
    const FLUSH_CACHE = 'systemCacheFlush';
    const STORE = 'store';
    const STORE_VIEW_COUNT = 'StoreViewCount';
    const WEBSITE = 'website';
    const WEBSITE_COUNT = 'WebsiteCount';
    const PRODUCT_CHANGE = 'adminProductChange';
    const PRODUCT_COUNT = 'productCatalogSize';
    const CONFIGURABLE_COUNT = 'productCatalogConfigurableSize';
    const ACTIVE_COUNT = 'productCatalogActiveSize';
    const CATEGORY_SIZE = 'productCatalogCategorySize';
    const CATEGORY_COUNT = 'CatalogCategoryCount';
    const ENABLED_MODULE_COUNT = 'enabledModuleCount';
    const MODULES_ENABLED = 'ModulesEnabled';
    const MODULES_DISABLED = 'ModulesDisabled';
    const MODULES_INSTALLED = 'ModulesInstalled';
    const MODULE_INSTALLED = 'moduleInstalled';
    const MODULE_UNINSTALLED = 'moduleUninstalled';
    const MODULE_ENABLED = 'moduleEnabled';
    const MODULE_DISABLED = 'moduleDisabled';
    /**#@-*/

    /**#@+
     * Text flags for states
     */
    const INSTALLED = 'installed';
    const UNINSTALLED = 'uninstalled';
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';
    const TRUE = 'true';
    const FALSE = 'false';
    /**#@-*/

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     * @since 2.0.0
     */
    protected $encryptor;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     * @since 2.0.0
     */
    protected $resourceConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
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
     * @since 2.0.0
     */
    public function isNewRelicEnabled()
    {
        return (bool)$this->scopeConfig->getValue('newrelicreporting/general/enable');
    }

    /**
     * Returns configured URL for API
     *
     * @return string
     * @since 2.0.0
     */
    public function getNewRelicApiUrl()
    {
        return (string)$this->scopeConfig->getValue('newrelicreporting/general/api_url');
    }

    /**
     * Returns configured URL for Insights API
     *
     * @return string
     * @since 2.0.0
     */
    public function getInsightsApiUrl()
    {
        return (string)$this->scopeConfig->getValue('newrelicreporting/general/insights_api_url');
    }

    /**
     * Returns configured account ID for New Relic
     *
     * @return string
     * @since 2.0.0
     */
    public function getNewRelicAccountId()
    {
        return (string)$this->scopeConfig->getValue('newrelicreporting/general/account_id');
    }

    /**
     * Return configured NR Application ID
     *
     * @return int
     * @since 2.0.0
     */
    public function getNewRelicAppId()
    {
        return (int)$this->scopeConfig->getValue('newrelicreporting/general/app_id');
    }

    /**
     * Returns configured API key for APM
     *
     * @return string
     * @since 2.0.0
     */
    public function getNewRelicApiKey()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue('newrelicreporting/general/api'));
    }

    /**
     * Returns configured Insights insert key for New Relic events related to cron jobs
     *
     * @return string
     * @since 2.0.0
     */
    public function getInsightsInsertKey()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue('newrelicreporting/general/insights_insert_key'));
    }

    /**
     * Returns configured NR Application name
     *
     * @return string
     * @since 2.0.0
     */
    public function getNewRelicAppName()
    {
        return (string)$this->scopeConfig->getValue('newrelicreporting/general/app_name');
    }

    /**
     * Returns config setting for overall cron to be enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isCronEnabled()
    {
        return (bool)$this->scopeConfig->getValue('newrelicreporting/cron/enable_cron');
    }

    /**
     * Sets config value
     *
     * @param string $pathId
     * @param mixed $value
     * @param string $scope
     * @param int $scopeId
     * @return void
     * @since 2.0.0
     */
    protected function setConfigValue($pathId, $value, $scope = 'default', $scopeId = 0)
    {
        $this->resourceConfig->saveConfig($pathId, $value, $scope, $scopeId);
    }

    /**
     * Disable module's functionality for case when new relic extension is not available
     *
     * @return void
     * @since 2.0.0
     */
    public function disableModule()
    {
        $this->setConfigValue('newrelicreporting/general/enable', 0);
    }
}
