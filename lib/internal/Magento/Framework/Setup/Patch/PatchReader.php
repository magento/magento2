<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Patch;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Module\Dir;

/**
 * Allows to read all patches through the whole system
 */
class PatchReader
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
     * @var string
     */
    private $type;

    /**
     * @param ComponentRegistrar $componentRegistrar
     * @param string $type
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        $type
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->type = $type;
    }

    /**
     * Retrieve absolute path to modules patch folder
     *
     * @param string $modulePath
     * @return string
     */
    private function getPatchFolder($modulePath)
    {
        return $modulePath . DIRECTORY_SEPARATOR . Dir::MODULE_SETUP_DIR .
            DIRECTORY_SEPARATOR . self::SETUP_PATCH_FOLDER;
    }

    /**
     * Retrieve module name prepared to usage in namespaces
     *
     * @param string $moduleName
     * @return string
     */
    private function getModuleNameForNamespace($moduleName)
    {
        return str_replace('_', '\\', $moduleName);
    }

    /**
     * Depends on type we want to handle schema and data patches in different folders
     *
     * @return string
     */
    private function getTypeFolder()
    {
        return ucfirst($this->type);
    }

    /**
     * Create array of class patch names from module name
     *
     * @param string $moduleName
     * @param string $modulePath
     * @return array
     */
    private function getPatchClassesPerModule($moduleName, $modulePath)
    {
        $patchClasses = [];
        $patchesPath = $this->getPatchFolder($modulePath);
        $specificPatchPath = $patchesPath . DIRECTORY_SEPARATOR . $this->getTypeFolder();
        $patchesMask = $specificPatchPath . DIRECTORY_SEPARATOR . '*.php';

        foreach (Glob::glob($patchesMask) as $patchPath) {
            $moduleName = $this->getModuleNameForNamespace($moduleName);
            $patchClasses[] = $moduleName . '\\Setup\\' .
                self::SETUP_PATCH_FOLDER . '\\' .
                $this->getTypeFolder() . '\\' .
                basename($patchPath, '.php');
        }

        return $patchClasses;
    }

    /**
     * @param string $moduleName
     * @return array
     */
    public function read($moduleName)
    {
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        $patches = $this->getPatchClassesPerModule($moduleName, $modulePath);
        return $patches;
    }
}
