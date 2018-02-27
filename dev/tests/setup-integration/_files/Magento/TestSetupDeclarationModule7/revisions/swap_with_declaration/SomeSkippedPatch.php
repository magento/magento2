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
class SomeSkippedPatch implements
    DataPatchInterface,
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
        return '2.0.2';
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

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
