<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\Code\Reader\Decorator;

use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Code\Reader\OrphanedPluginList;
use Magento\Framework\Exception\FileSystemException;

class Area implements \Magento\Setup\Module\Di\Code\Reader\ClassesScannerInterface
{
    /**
     * @param ClassesScanner $classesScanner
     * @param ClassReaderDecorator $classReaderDecorator
     * @param OrphanedPluginList $orphanedPluginList
     */
    public function __construct(
        private readonly ClassesScanner $classesScanner,
        private readonly ClassReaderDecorator $classReaderDecorator,
        private readonly OrphanedPluginList $orphanedPluginList
    ) {
    }

    /**
     * Retrieves list of classes for given path
     *
     * @param string $path path to dir with files
     *
     * @return array
     * @throws FileSystemException
     */
    public function getList($path)
    {
        $classes = [];
        foreach ($this->classesScanner->getList($path) as $className) {
            if ($this->orphanedPluginList->isOrphanedPlugin($className)) {
                // Skip constructor resolution for plugins that are only attached
                // to non-existing target classes. Their DI will never be exercised
                // because no interceptor is generated for missing targets.
                continue;
            }
            $classes[$className] = (array) $this->classReaderDecorator->getConstructor($className);
        }

        return $classes;
    }
}
