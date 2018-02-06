<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Patch;

use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * This interface describe script, that atomic operations with data (DML, DQL) in SQL database
 */
interface DataPatchInterface extends PatchDisableInterface
{
    /**
     * Provide system ugrade or install
     *
     * @return void
     */
    public function apply(ModuleDataSetupInterface $moduleDataSetup);

    /**
     * Provide system downgrade
     *
     * @return void
     */
    public function revert(ModuleDataSetupInterface $moduleDataSetup);
}