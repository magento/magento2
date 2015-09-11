<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\FileResolver;

/**
 * Utility class for populating an autoloader with application-specific information for PSR-0 and PSR-4 mappings
 * and include-path contents
 */
class Populator
{
    /**
     * @param AutoloaderInterface $autoloader
     * @param DirectoryList $dirList
     * @param ComponentRegistrar $componentRegistrar
     * @return void
     */
    public static function populateMappings(
        AutoloaderInterface $autoloader,
        DirectoryList $dirList,
        ComponentRegistrar $componentRegistrar
    ) {
        $generationDir = $dirList->getPath(DirectoryList::GENERATION);
        $frameworkDir = $dirList->getPath(DirectoryList::LIB_INTERNAL);

        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            $autoloader->addPsr4(str_replace('_', '\\', $moduleName) . '\\', [$moduleDir . '/'], true);
        }

        $autoloader->addPsr4('Magento\\', [$generationDir . '/Magento/'], true);
        $autoloader->addPsr0('Apache_', $frameworkDir, true);
        $autoloader->addPsr0('Cm_', $frameworkDir, true);
        $autoloader->addPsr0('Credis_', $frameworkDir, true);
        $autoloader->addPsr0('Less_', $frameworkDir, true);
        $autoloader->addPsr0('Symfony\\', $frameworkDir, true);

        /** Required for Zend functionality */
        FileResolver::addIncludePath($frameworkDir);

        /** Required for code generation to occur */
        FileResolver::addIncludePath($generationDir);

        /** Required to autoload custom classes */
        $autoloader->addPsr0('', [$generationDir]);
    }
}
