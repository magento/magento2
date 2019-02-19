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
 * Class InstallData
 * @package Magento\TestSetupDeclarationModule3\Setup
 */
class IncrementalSomeIntegerPatch implements
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
        return '1.0.5';
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
        $select = $adapter->select()->from('test_table', 'varchar')
            ->where('`smallint` = ?', 1);
        $refSelect = $adapter->select()->from('reference_table', 'for_patch_testing')
            ->where('`tinyint_ref` = ?', 7);
        $varchar = $adapter->fetchOne($select);
        $varchar2 = $adapter->fetchOne($refSelect);
        $adapter->insert('test_table', ['varchar' => $varchar . "_ref", 'varbinary' => 0101010]);
        $adapter->insert('test_table', ['varchar' => $varchar2, 'varbinary' => 0]);
    }

    public function revert()
    {
        $adapter = $this->resourceConnection->getConnection();
        $select = $adapter->select()->from('test_table', 'varchar')
            ->where('`smallint` = ?', 1);
        $varchar = $adapter->fetchOne($select);
        $refSelect = $adapter->select()->from('reference_table', 'for_patch_testing')
            ->where('`tinyint_ref` = ?', 7);
        $varchar2 = $adapter->fetchOne($refSelect);
        $adapter->delete('test_table', ['`varchar` = ?' => $varchar . "_ref"]);
        $adapter->delete('test_table', ['`varchar` = ?' => $varchar2]);
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [
            ReferenceIncrementalSomeIntegerPatch::class,
            NextChainPatch::class
        ];
    }
}
