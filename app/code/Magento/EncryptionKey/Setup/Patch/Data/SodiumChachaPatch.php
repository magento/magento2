<?php

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
     * @var \Magento\Framework\Config\ScopeInterface
     */
    private $scope;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Config\Model\Config\Structure\Proxy $structure
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Config\ScopeInterface $scope
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Config\Model\Config\Structure\Proxy $structure,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Config\ScopeInterface $scope
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->structure = $structure;
        $this->encryptor = $encryptor;
        $this->scope = $scope;
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
        $currentScope = $this->scope->getCurrentScope();

        $this->scope->setCurrentScope(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $paths = $this->structure->getFieldPathsByAttribute(
            'backend_model',
            \Magento\Config\Model\Config\Backend\Encrypted::class
        );

        $this->scope->setCurrentScope($currentScope);

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
