<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile - as of namespace absence

class OtherDataPatch implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
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

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
    }
}

class SomeDataPatch implements
    \Magento\Framework\Setup\Patch\DataPatchInterface,
    \Magento\Framework\Setup\Patch\PatchVersionInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            OtherDataPatch::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }
}

class NonTransactionableDataPatch implements
    \Magento\Framework\Setup\Patch\DataPatchInterface,
    \Magento\Framework\Setup\Patch\NonTransactionableInterface
{

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

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
    }
}

class RevertableDataPatch implements
    \Magento\Framework\Setup\Patch\DataPatchInterface,
    \Magento\Framework\Setup\Patch\PatchRevertableInterface
{
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

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
    }
}
