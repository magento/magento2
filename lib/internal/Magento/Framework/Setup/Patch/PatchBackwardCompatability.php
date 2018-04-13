<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Patch;

use Magento\Framework\Module\ModuleResource;

/**
 * This class is information expert in questions of backward compatibility in data and schema patches
 */
class PatchBackwardCompatability
{
    /**
     * @var ModuleResource
     */
    private $moduleResource;

    /**
     * @param ModuleResource $moduleResource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(ModuleResource $moduleResource)
    {
        $this->moduleResource = $moduleResource;
    }

    /**
     * Check is patch skipable by data setup version in DB
     *
     * @param string $patchClassName
     * @param string $moduleName
     * @return bool
     */
    public function isSkipableByDataSetupVersion(string $patchClassName, string $moduleName) : bool
    {
        $dbVersion = (string) $this->moduleResource->getDataVersion($moduleName);
        return in_array(PatchVersionInterface::class, class_implements($patchClassName)) &&
            version_compare(call_user_func([$patchClassName, 'getVersion']), $dbVersion) <= 0;
    }

    /**
     * Check is patch skipable by schema setup version in DB
     *
     * @param string $patchClassName
     * @param string $moduleName
     * @return bool
     */
    public function isSkipableBySchemaSetupVersion(string $patchClassName, string $moduleName) : bool
    {
        $dbVersion = (string) $this->moduleResource->getDbVersion($moduleName);
        return in_array(PatchVersionInterface::class, class_implements($patchClassName)) &&
            version_compare(call_user_func([$patchClassName, 'getVersion']), $dbVersion) <= 0;
    }
}
