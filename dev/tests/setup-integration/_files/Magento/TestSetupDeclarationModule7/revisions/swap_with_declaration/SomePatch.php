<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestSetupDeclarationModule7\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchRevertableInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class SomePatch
 * @package Magento\TestSetupDeclarationModule3\Setup
 */
class SomePatch implements
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
        return '2.0.4';
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
        $adapter->insert('test_table', ['varchar' => "_ref"]);
    }

    public function revert()
    {
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
