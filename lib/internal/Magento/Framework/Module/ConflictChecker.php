<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

use Magento\Framework\Filesystem;

class ConflictChecker extends Checker
{
    /**
     * Key to conflicting packages array in composer.json files
     */
    const KEY_CONFLICT = 'conflict';

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     * @param Mapper $mapper
     */
    public function __construct(Filesystem $filesystem, Mapper $mapper)
    {
        parent::__construct($mapper);
        $this->filesystem = $filesystem;
    }

    /**
     * Check if enabling module will conflict any modules
     *
     * @param $moduleName
     * @return array
     */
    public function checkConflictWhenEnableModule($moduleName)
    {
        $conflicts = [];
        foreach ($this->enabledModules as $enabledModule) {
            if ($this->checkIfConflict($enabledModule, $moduleName)) {
                $conflicts[] = $enabledModule;
            }
        }
        return $conflicts;
    }

    /**
     * Check if module is conflicted
     *
     * @param string $moduleName
     * @return bool
     */
    private function checkIfConflict($enabledModule, $moduleName)
    {
        $jsonDecoder = new \Magento\Framework\Json\Decoder();

        $vendorA = $this->mapper->moduleFullNameToVendorName($enabledModule);
        $vendorB = $this->mapper->moduleFullNameToVendorName($moduleName);
        $moduleA = $this->mapper->moduleFullNameToModuleName($enabledModule);
        $moduleB = $this->mapper->moduleFullNameToModuleName($moduleName);

        $readAdapter = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MODULES);

        $data1 = $jsonDecoder->decode($readAdapter->readFile("$vendorA/$moduleA/composer.json"));
        $data2 = $jsonDecoder->decode($readAdapter->readFile("$vendorB/$moduleB/composer.json"));

        if (isset($data1[self::KEY_CONFLICT])) {
            foreach (array_keys($data1[self::KEY_CONFLICT]) as $packageName) {
                $module = $this->mapper->packageNameToModuleFullName($packageName);
                if ($module == $moduleName) {
                    return true;
                }
            }
        }

        if (isset($data2[self::KEY_CONFLICT])) {
            foreach (array_keys($data2[self::KEY_CONFLICT]) as $packageName) {
                $module = $this->mapper->packageNameToModuleFullName($packageName);
                if ($module == $enabledModule) {
                    return true;
                }
            }
        }
        return false;
    }
}
