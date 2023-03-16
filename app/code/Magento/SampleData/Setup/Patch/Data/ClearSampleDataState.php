<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Setup\Patch\Data;

use Magento\Framework\Setup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\SampleData\State;

/**
 * Class ClearSampleDataState
 * @package Magento\SampleData\Setup\Patch
 */
class ClearSampleDataState implements DataPatchInterface, PatchVersionInterface
{
    /**
     * ClearSampleDataState constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param State $state
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly State $state
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->state->clearState();
    }

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
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
