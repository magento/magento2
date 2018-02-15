<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile - as of namespace absence

class OtherSchemaPatch implements \Magento\Setup\Model\Patch\SchemaPatchInterface
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

class SomeSchemaPatch implements
    \Magento\Setup\Model\Patch\SchemaPatchInterface,
    \Magento\Setup\Model\Patch\PatchVersionInterface
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
