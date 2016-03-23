<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Autoload\AutoloaderInterface;
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
     * @return void
     */
    public static function populateMappings(AutoloaderInterface $autoloader, DirectoryList $dirList)
    {
        $generationDir = $dirList->getPath(DirectoryList::GENERATION);
        $frameworkDir = $dirList->getPath(DirectoryList::LIB_INTERNAL);

        $autoloader->addPsr4('Magento\\', [$generationDir . '/Magento/'], true);

        $autoloader->addPsr0('Cm_', $frameworkDir, true);
        $autoloader->addPsr0('Credis_', $frameworkDir, true);

        /** Required for Zend functionality */
        FileResolver::addIncludePath($frameworkDir);

        /** Required for code generation to occur */
        FileResolver::addIncludePath($generationDir);

        /** Required to autoload custom classes */
        $autoloader->addPsr0('', [$generationDir]);
    }
}
