<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestSetupDeclarationModule5\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class SomePatch
 * @package Magento\TestSetupDeclarationModule3\Setup
 */
class SomeSkippedPatch implements
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
        return '1.0.2';
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
        throw new \Exception('This patch should be skipped!');
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
