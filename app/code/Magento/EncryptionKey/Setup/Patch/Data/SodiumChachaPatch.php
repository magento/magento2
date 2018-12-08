<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Migrate encrypted configuration values to the latest cipher
 */
class SodiumChachaPatch implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    private $structure;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Config\Model\Config\Structure\Proxy $structure
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Config\Model\Config\Structure\Proxy $structure,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->structure = $structure;
        $this->encryptor = $encryptor;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->reEncryptSystemConfigurationValues();

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    private function reEncryptSystemConfigurationValues()
    {
        $structure = $this->structure;
        $paths = $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            function () use ($structure) {
                return $structure->getFieldPathsByAttribute(
                    'backend_model',
                    \Magento\Config\Model\Config\Backend\Encrypted::class
                );
            }
        );
        // walk through found data and re-encrypt it
        if ($paths) {
            $table = $this->moduleDataSetup->getTable('core_config_data');
            $values = $this->moduleDataSetup->getConnection()->fetchPairs(
                $this->moduleDataSetup->getConnection()
                    ->select()
                    ->from($table, ['config_id', 'value'])
                    ->where('path IN (?)', $paths)
                    ->where('value NOT LIKE ?', '')
            );
            foreach ($values as $configId => $value) {
                $this->moduleDataSetup->getConnection()->update(
                    $table,
                    ['value' => $this->encryptor->encrypt($this->encryptor->decrypt($value))],
                    ['config_id = ?' => (int)$configId]
                );
            }
        }
    }
}
