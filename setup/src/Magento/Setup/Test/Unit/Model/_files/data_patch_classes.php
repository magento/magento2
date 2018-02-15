<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class OtherDataPatch implements \Magento\Setup\Model\Patch\DataPatchInterface
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

class SomeDataPatch implements \Magento\Setup\Model\Patch\DataPatchInterface
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
}
