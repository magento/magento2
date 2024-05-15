<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Patch;

/**
 * Each patch can have dependencies, that should be applied before such patch
 *
 *            /  Patch2 --- Patch3
 *          /
 *        /
 * Patch1
 *
 * Here you see dependency of Patch1 to Patch2
 *
 * @api
 */
interface DependentPatchInterface
{
    /**
     * Get array of patches that have to be executed prior to this.
     *
     * Example of implementation:
     *
     * [
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch1::class,
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch2::class
     * ]
     *
     * @return string[]
     */
    public static function getDependencies();
}
