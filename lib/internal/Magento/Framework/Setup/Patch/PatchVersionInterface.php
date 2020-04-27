<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Patch;

/**
 * For backward compatibility with versioned style module installation.
 * The interface should be used for migration from the legacy installation approach to the declarative installation
 * mechanism. The usage of this interface prohibited for the new data or schema patches.
 */
interface PatchVersionInterface
{
    /**
     * This version associate patch with Magento setup version.
     * For example, if Magento current setup version is 2.0.3 and patch version is 2.0.2 then
     * this patch will be added to registry, but will not be applied, because it is already applied
     * by old mechanism of UpgradeData.php script
     *
     * @return string
     */
    public static function getVersion();
}
