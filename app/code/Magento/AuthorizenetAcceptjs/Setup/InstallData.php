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
 * @package Magento\AuthorizenetAcceptjs\Setup
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

    const DIRECTPOST_PATH = 'payment/authorizenet_directpost';
    const ACCEPTJS_PATH = 'payment/authorizenet_acceptjs';
    const PAYMENT_PATH_FORMAT = '%s/%s';

    /**
     * InstallData constructor.
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
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) :void
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
            $configValue = $this->getConfigValue($field);

            if ($configValue !== null && !empty($configValue)) {
                $this->saveConfigValue($field, $configValue);
            }
        }

        foreach ($encryptedConfigFieldsToMigrate as $field) {
            $configValue = $this->getConfigValue($field);

            if ($configValue !== null && !empty($configValue)) {
                $this->saveConfigValue($field, $configValue, true);
            }
        }

        $setup->endSetup();
    }

    /**
     * @param $field
     * @return mixed
     */
    protected function getConfigValue($field)
    {
        return $this->scopeConfig->getValue(sprintf(self::PAYMENT_PATH_FORMAT, self::DIRECTPOST_PATH, $field), ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $field
     * @param $value
     * @param bool $isEncrypted
     */
    protected function saveConfigValue($field, $value, $isEncrypted = false) :void
    {
        $value = $isEncrypted ? $this->encryptor->encrypt($value) : $value;
        $this->resourceConfig->saveConfig(sprintf(self::PAYMENT_PATH_FORMAT, self::ACCEPTJS_PATH, $field), $value, ScopeInterface::SCOPE_STORE);
    }
}
