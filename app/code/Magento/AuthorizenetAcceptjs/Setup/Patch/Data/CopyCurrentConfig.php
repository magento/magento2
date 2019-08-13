<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Copies the Authorize.net DirectPost configuration values to the new Accept.js module.
 */
class CopyCurrentConfig implements DataPatchInterface
{
    private const DIRECTPOST_PATH = 'payment/authorizenet_directpost';
    private const ACCEPTJS_PATH = 'payment/authorizenet_acceptjs';
    private const PAYMENT_PATH_FORMAT = '%s/%s';

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
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

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
        'max_order_total'
    ];

    /**
     * @var array
     */
    private $encryptedConfigFieldsToMigrate = [
        'login',
        'trans_key',
        'trans_md5'
    ];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $resourceConfig
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ScopeConfigInterface $scopeConfig,
        ConfigInterface  $resourceConfig,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->encryptor = $encryptor;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $this->migrateDefaultValues();
        $this->migrateWebsiteValues();
        $this->moduleDataSetup->endSetup();
    }

    /**
     * Migrate configuration values from DirectPost to Accept.js on default scope
     *
     * @return void
     */
    private function migrateDefaultValues(): void
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
     */
    private function migrateWebsiteValues(): void
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteID = (int) $website->getId();

            foreach ($this->configFieldsToMigrate as $field) {
                $configValue = $this->getOldConfigValue($field, ScopeInterface::SCOPE_WEBSITES, $websiteID);

                if (!empty($configValue)) {
                    $this->saveNewConfigValue($field, $configValue, ScopeInterface::SCOPE_WEBSITES, $websiteID);
                }
            }

            foreach ($this->encryptedConfigFieldsToMigrate as $field) {
                $configValue = $this->getOldConfigValue($field, ScopeInterface::SCOPE_WEBSITES, $websiteID);

                if (!empty($configValue)) {
                    $this->saveNewConfigValue($field, $configValue, ScopeInterface::SCOPE_WEBSITES, $websiteID, true);
                }
            }
        }
    }

    /**
     * Get old configuration value from the DirectPost module's configuration on the store scope
     *
     * @param string $field
     * @param string $scope
     * @param int $scopeID
     * @return mixed
     */
    private function getOldConfigValue(
        string $field,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeID = null
    ) {
        return $this->scopeConfig->getValue(
            sprintf(self::PAYMENT_PATH_FORMAT, self::DIRECTPOST_PATH, $field),
            $scope,
            $scopeID
        );
    }

    /**
     * Save configuration value for AcceptJS
     *
     * @param string $field
     * @param mixed $value
     * @param string $scope
     * @param int $scopeID
     * @param bool $isEncrypted
     * @return void
     */
    private function saveNewConfigValue(
        string $field,
        $value,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeID = 0,
        bool $isEncrypted = false
    ): void {
        $value = $isEncrypted ? $this->encryptor->encrypt($value) : $value;

        $this->resourceConfig->saveConfig(
            sprintf(self::PAYMENT_PATH_FORMAT, self::ACCEPTJS_PATH, $field),
            $value,
            $scope,
            $scopeID
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
