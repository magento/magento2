<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Copies the Authorize.net DirectPost configuration values to the new Accept.js module.
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var string
     */
    private static $directpostPath = 'payment/authorizenet_directpost';

    /**
     * @var string
     */
    private static $acceptjsPath = 'payment/authorizenet_acceptjs';

    /**
     * @var string
     */
    private static $paymentPathFormat = '%s/%s';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $configFieldsToMigrate = [
        'cctypes',
        'debug',
        'email_customer',
        'order_status',
        'payment_action',
        'currency',
        'allow_specific',
        'specificcountry',
        'min_order_total',
        'max_order_total',
    ];

    /**
     * @var array
     */
    private $encryptedConfigFieldsToMigrate = [
        'login',
        'trans_key',
        'trans_md5',
    ];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $resourceConfig
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface  $resourceConfig,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->migrateDefaultValues();
        $this->migrateWebsiteValues();
        $setup->endSetup();
    }

    /**
     * Migrate configuration values from DirectPost to Accept.js on default scope
     *
     * @return void
     */
    private function migrateDefaultValues()
    {
        foreach ($this->configFieldsToMigrate as $field) {
            $configValue = $this->getOldConfigValue($field);

            if (!empty($configValue)) {
                $this->saveNewConfigValue($field, $configValue);
            }
        }

        foreach ($this->encryptedConfigFieldsToMigrate as $field) {
            $configValue = $this->getOldConfigValue($field);

            if (!empty($configValue)) {
                $this->saveNewConfigValue(
                    $field,
                    $configValue,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    0,
                    true
                );
            }
        }
    }

    /**
     * Migrate configuration values from DirectPost to Accept.js on all website scopes
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function migrateWebsiteValues()
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteId = (int)$website->getId();

            foreach ($this->configFieldsToMigrate as $field) {
                $defaultConfigValue = $this->getOldConfigValue($field);
                $configValue = $this->getOldConfigValue($field, ScopeInterface::SCOPE_WEBSITES, $websiteId);
                if (!empty($configValue) && !empty($defaultConfigValue) && $configValue !== $defaultConfigValue) {
                    $this->saveNewConfigValue($field, $configValue, ScopeInterface::SCOPE_WEBSITES, $websiteId);
                }
            }

            foreach ($this->encryptedConfigFieldsToMigrate as $field) {
                $defaultConfigValue = $this->getOldConfigValue($field);
                $configValue = $this->getOldConfigValue($field, ScopeInterface::SCOPE_WEBSITES, $websiteId);
                if (!empty($configValue) && !empty($defaultConfigValue) && $configValue !== $defaultConfigValue) {
                    $this->saveNewConfigValue($field, $configValue, ScopeInterface::SCOPE_WEBSITES, $websiteId, true);
                }
            }
        }
    }

    /**
     * Get old configuration value from the DirectPost module's configuration on the store scope
     *
     * @param string $field
     * @param string $scope
     * @param int $scopeId
     * @return mixed
     */
    private function getOldConfigValue(
        string $field,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = null
    ) {
        return $this->scopeConfig->getValue(
            sprintf(self::$paymentPathFormat, self::$directpostPath, $field),
            $scope,
            $scopeId
        );
    }

    /**
     * Save configuration value for AcceptJS
     *
     * @param string $field
     * @param mixed $value
     * @param string $scope
     * @param int $scopeId
     * @param bool $isEncrypted
     * @return void
     */
    private function saveNewConfigValue(
        string $field,
        $value,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0,
        bool $isEncrypted = false
    ) {
        $value = $isEncrypted ? $this->encryptor->encrypt($value) : $value;

        $this->resourceConfig->saveConfig(
            sprintf(self::$paymentPathFormat, self::$acceptjsPath, $field),
            $value,
            $scope,
            $scopeId
        );
    }
}
