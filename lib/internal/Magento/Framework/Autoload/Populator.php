<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Autoload;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\FileResolver;

/**
 * Utility class for populating an autoloader with application-specific information for PSR-0 and PSR-4 mappings
 * and include-path contents
 * @since 2.0.0
 */
class Populator
{
    /**
     * @param AutoloaderInterface $autoloader
     * @param DirectoryList $dirList
     * @return void
     * @since 2.0.0
     */
    public static function populateMappings(AutoloaderInterface $autoloader, DirectoryList $dirList)
    {
        $generationDir = $dirList->getPath(DirectoryList::GENERATED_CODE);

        $autoloader->addPsr4('Magento\\', [$generationDir . '/Magento/'], true);

        /** Required for code generation to occur */
        FileResolver::addIncludePath($generationDir);

        /** Required to autoload custom classes */
        $autoloader->addPsr0('', [$generationDir]);
    }
}
