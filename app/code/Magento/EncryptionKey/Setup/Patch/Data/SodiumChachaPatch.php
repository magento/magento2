<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Migrate encrypted configuration values to the latest cipher
 */
class SodiumChachaPatch implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    private $scope;

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
     * SodiumChachaPatch constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Config\Model\Config\Structure\Proxy $structure
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Config\ScopeInterface|null $scope
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Config\Model\Config\Structure\Proxy $structure,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Config\ScopeInterface $scope = null
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->structure = $structure;
        $this->encryptor = $encryptor;
        $this->state = $state;
        $this->scope = $scope ?? ObjectManager::getInstance()->get(\Magento\Framework\Config\ScopeInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->reEncryptSystemConfigurationValues();

        $this->moduleDataSetup->endSetup();
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

    /**
     * Re encrypt sensitive data in the system configuration
     */
    private function reEncryptSystemConfigurationValues()
    {
        $table = $this->moduleDataSetup->getTable('core_config_data');
        $hasEncryptedData = $this->moduleDataSetup->getConnection()->fetchOne(
            $this->moduleDataSetup->getConnection()
                ->select()
                ->from($table, [new \Zend_Db_Expr('count(value)')])
                ->where('value LIKE ?', '0:2%')
        );
        if ($hasEncryptedData !== '0') {
            $currentScope = $this->scope->getCurrentScope();
            $structure = $this->structure;
            $paths = $this->state->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML,
                function () use ($structure) {
                    $this->scope->setCurrentScope(\Magento\Framework\App\Area::AREA_ADMINHTML);
                    /** Returns list of structure paths to be re encrypted */
                    $paths = $structure->getFieldPathsByAttribute(
                        'backend_model',
                        \Magento\Config\Model\Config\Backend\Encrypted::class
                    );
                    /** Returns list of mapping between configPath => [structurePaths] */
                    $mappedPaths = $structure->getFieldPaths();
                    foreach ($mappedPaths as $mappedPath => $data) {
                        foreach ($data as $structurePath) {
                            if ($structurePath !== $mappedPath && $key = array_search($structurePath, $paths)) {
                                $paths[$key] = $mappedPath;
                            }
                        }
                    }

                    return array_unique($paths);
                }
            );
            $this->scope->setCurrentScope($currentScope);
            // walk through found data and re-encrypt it
            if ($paths) {
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
}
