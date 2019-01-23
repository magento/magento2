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

/**
 * Class InstallData
 *
 * Migrates Authorize.net DirectPost configuration values to the new Accept.js module.
 */
class InstallData implements InstallDataInterface
{
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

    private const DIRECTPOST_PATH = 'payment/authorizenet_directpost';
    private const ACCEPTJS_PATH = 'payment/authorizenet_acceptjs';
    private const PAYMENT_PATH_FORMAT = '%s/%s';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $resourceConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface  $resourceConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * Migrate DirectPost values in store scope to Accept.js
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        $configFieldsToMigrate = [
            'cctypes',
            'debug',
            'email_customer',
            'order_status',
            'payment_action',
            'currency',
            'allow_specific'
        ];

        $encryptedConfigFieldsToMigrate = [
            'login',
            'trans_key',
            'trans_md5'
        ];

        foreach ($configFieldsToMigrate as $field) {
            $configValue = $this->getOldConfigValue($field);

            if (!empty($configValue)) {
                $this->saveNewConfigValue($field, $configValue);
            }
        }

        foreach ($encryptedConfigFieldsToMigrate as $field) {
            $configValue = $this->getOldConfigValue($field);

            if (!empty($configValue)) {
                $this->saveNewConfigValue($field, $configValue, true);
            }
        }

        $setup->endSetup();
    }

    /**
     * Get old configuration value from the DirectPost module's configuration on the store scope
     *
     * @param string $field
     * @return mixed
     */
    private function getOldConfigValue(string $field)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::PAYMENT_PATH_FORMAT, self::DIRECTPOST_PATH, $field),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Save configuration value for AcceptJS
     *
     * @param string $field
     * @param mixed $value
     * @param bool $isEncrypted
     */
    private function saveNewConfigValue(string $field, $value, $isEncrypted = false): void
    {
        $value = $isEncrypted ? $this->encryptor->encrypt($value) : $value;
        $this->resourceConfig->saveConfig(
            sprintf(self::PAYMENT_PATH_FORMAT, self::ACCEPTJS_PATH, $field),
            $value,
            ScopeInterface::SCOPE_STORE
        );
    }
}
