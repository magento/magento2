<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestSetupDeclarationModule3\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * @package Magento\TestSetupDeclarationModule3\Setup
 */
class LlNextChainPatch implements
    DataPatchInterface,
    PatchRevertableInterface,
    PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * IncrementalSomeIntegerPatch constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '0.0.5';
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $adapter = $this->resourceConnection->getConnection();
        $adapter->insertArray('reference_table', ['for_patch_testing'], ['very_secret_string']);
    }

    public function revert()
    {
        $adapter = $this->resourceConnection->getConnection();
        $adapter->delete('reference_table', ['for_patch_testing = ?' => 'very_secret_string']);
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [
            ZFirstPatch::class
        ];
    }
}
