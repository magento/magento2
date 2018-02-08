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
     * Prepare path to patch folder: schema or data
     *
     * @param string $moduleName
     * @return string
     */
    private function getPathToPatchFolder($moduleName)
    {
        return str_replace('_', '\\', $moduleName) .
            '\Setup\Patch' .
            ucfirst($this->type) . '\\';
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
        $patchesPath = $modulePath . DIRECTORY_SEPARATOR . Dir::MODULE_SETUP_DIR .
            DIRECTORY_SEPARATOR . self::SETUP_PATCH_FOLDER;
        $patchesPath = $patchesPath . $this->getPathToPatchFolder($moduleName);

        foreach (Glob::glob($patchesPath) as $patchPath) {
            $patchClasses[] = $modulePath . basename($patchPath, '.php');
        }

        return $patchClasses;
    }

    /**
     * @param null $moduleName
     * @return array
     */
    public function read($moduleName = null)
    {
        $patches = [
            $this->type => []
        ];

        if ($moduleName === null) {
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $modulePath) {
                $patches[$this->type] += $this->getPatchClassesPerModule($moduleName, $modulePath);
            }
        } else {
            $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
            $patches[$this->type] += $this->getPatchClassesPerModule($moduleName, $modulePath);
        }

        return $patches;
    }
}
