<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Module\Dir;

/**
 * Allows to read all patches through the whole system
 */
class PatchReader implements ReaderInterface
{
    /**
     * Folder name, where patches are
     */
    const SETUP_PATCH_FOLDER = 'Patch';

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar
    ) {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Create array of class patch names from module name
     *
     * @param string $moduleName
     * @param string $modulePath
     * @return array
     */
    private function getDataPatchClassesPerModule($moduleName, $modulePath)
    {
        $patchClasses = [];
        $patchesPath = $modulePath . DIRECTORY_SEPARATOR . Dir::MODULE_SETUP_DIR .
            DIRECTORY_SEPARATOR . self::SETUP_PATCH_FOLDER;
        $modulePath = str_replace('_', '\\', $moduleName) . '\Setup\Patch\Data\\';

        foreach (Glob::glob($patchesPath) as $patchPath) {
            $patchClasses[] = $modulePath . basename($patchPath, '.php');
        }

        return $patchClasses;
    }

    /**
     * Create array of class patch names from module name
     *
     * @param string $moduleName
     * @param string $modulePath
     * @return array
     */
    private function getSchemaPatchClassesPerModule($moduleName, $modulePath)
    {
        $patchClasses = [];
        $patchesPath = $modulePath . DIRECTORY_SEPARATOR . Dir::MODULE_SETUP_DIR .
            DIRECTORY_SEPARATOR . self::SETUP_PATCH_FOLDER;
        $modulePath = str_replace('_', '\\', $moduleName) . '\Setup\Patch\Schema\\';

        foreach (Glob::glob($patchesPath) as $patchPath) {
            $patchClasses[] = $modulePath . basename($patchPath, '.php');
        }

        return $patchClasses;
    }

    /**
     * @param null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $patches = ['schema' => [], 'data' => []];
        if ($scope === null) {
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $modulePath) {
                $patches['schema'] += $this->getDataPatchClassesPerModule($moduleName, $modulePath);
                $patches['data'] += $this->getSchemaPatchClassesPerModule($moduleName, $modulePath);
            }
        } else {
            $patches['schema'] = $this->getSchemaPatchClassesPerModule(
                $scope,
                $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $scope)
            );
            $patches['data'] = $this->getDataPatchClassesPerModule(
                $scope,
                $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $scope)
            );
        }

        return $patches;
    }
}
