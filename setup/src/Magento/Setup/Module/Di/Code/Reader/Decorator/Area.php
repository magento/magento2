<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\Code\Reader\Decorator;

use Magento\Setup\Module\Di\Code\Reader\ClassesScanner;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Interception\PluginListGenerator;

readonly class Area implements \Magento\Setup\Module\Di\Code\Reader\ClassesScannerInterface
{
    /**
     * @param ClassesScanner $classesScanner
     * @param ClassReaderDecorator $classReaderDecorator
     * @param PluginListGenerator $pluginListGenerator
     */
    public function __construct(
        private ClassesScanner $classesScanner,
        private ClassReaderDecorator $classReaderDecorator,
        private PluginListGenerator $pluginListGenerator
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
            if ($this->pluginListGenerator->isOrphanedPlugin($className)) {
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
