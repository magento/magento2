<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Patch;

/**
 * This interface describe script, that is presented by atomic operations for data and schema
 */
interface PatchInterface extends DependentPatchInterface
{
    /**
     * This version associate patch with Magento setup version.
     * For example, if Magento current setup version is 2.0.3 and patch version is 2.0.2 than
     * this patch will be added to registry, but will not be applied, because it is already applied
     * by old mechanism of UpgradeData.php script
     *
     *
     * @return string
     * @deprecated since appearance, required for backward compatibility
     */
    public function getVersion();

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
