<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EncryptionKey\Setup\Patch\Data;

use Magento\Config\Model\Config\Backend\Encrypted;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Migrate encrypted configuration values to the latest cipher
 */
class SodiumChachaPatch implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Structure
     */
    private $structure;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var State
     */
    private $state;

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * SodiumChachaPatch constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Structure $structure
     * @param EncryptorInterface $encryptor
     * @param State $state
     * @param ScopeInterface $scope
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Structure $structure,
        EncryptorInterface $encryptor,
        State $state,
        ScopeInterface $scope
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->structure = $structure;
        $this->encryptor = $encryptor;
        $this->state = $state;
        $this->scope = $scope;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->reEncryptSystemConfigurationValues();

        $this->moduleDataSetup->endSetup();

        return $this;
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
                Area::AREA_ADMINHTML,
                function () use ($structure) {
                    $this->scope->setCurrentScope(Area::AREA_ADMINHTML);
                    /** Returns list of structure paths to be re encrypted */
                    $paths = $structure->getFieldPathsByAttribute(
                        'backend_model',
                        Encrypted::class
                    );
                    /** Returns list of mapping between configPath => [structurePaths] */
                    $mappedPaths = $structure->getFieldPaths();
                    foreach ($mappedPaths as $mappedPath => $data) {
                        foreach ($data as $structurePath) {
                            if ($structurePath === $mappedPath) {
                                continue;
                            }

                            $key = array_search($structurePath, $paths);

                            if ($key) {
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
