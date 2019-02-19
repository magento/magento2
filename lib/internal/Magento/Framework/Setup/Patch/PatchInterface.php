<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Patch;

/**
 * This interface describe script, that is presented by atomic operations for data and schema
 */
interface PatchInterface extends DependentPatchInterface
{
    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases();

    /**
     * Run code inside patch
     * If code fails, patch must be reverted, in case when we are speaking about schema - than under revert
     * means run PatchInterface::revert()
     *
     * If we speak about data, under revert means: $transaction->rollback()
     *
     * @return $this
     */
    public function apply();
}
