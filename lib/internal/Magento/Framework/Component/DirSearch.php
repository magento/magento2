<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Component;

use Magento\Framework\Filesystem;

/**
 * Class for searching files across all locations of certain component type
 */
class DirSearch
{
    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     */
    private $registrar;

    /**
     * Read dir factory
     *
     * @var Filesystem\Directory\ReadFactory
     */
    private $readFactory;

    /**
     * Constructor
     *
     * @param ComponentRegistrarInterface $registrar
     * @param Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(ComponentRegistrarInterface $registrar, Filesystem\Directory\ReadFactory $readFactory)
    {
        $this->registrar = $registrar;
        $this->readFactory = $readFactory;
    }

    /**
     * Search for files in each component by pattern, returns absolute paths
     *
     * @param string $componentType
     * @param string $pattern
     * @return array
     */
    public function collectFiles($componentType, $pattern)
    {
        return $this->collect($componentType, $pattern, false);
    }

    /**
     * Search for files in each component by pattern, returns file objects with absolute file paths
     *
     * @param string $componentType
     * @param string $pattern
     * @return ComponentFile[]
     */
    public function collectFilesWithContext($componentType, $pattern)
    {
        return $this->collect($componentType, $pattern, true);
    }

    /**
     * Collect files in components
     * If $withContext is true, returns array of file objects with component context
     *
     * @param string $componentType
     * @param string $pattern
     * @param bool|false $withContext
     * @return array
     */
    private function collect($componentType, $pattern, $withContext)
    {
        $files = [];
        foreach ($this->registrar->getPaths($componentType) as $componentName => $path) {
            $directoryRead = $this->readFactory->create($path);
            $foundFiles = $directoryRead->search($pattern);
            foreach ($foundFiles as $foundFile) {
                $foundFile = $directoryRead->getAbsolutePath($foundFile);
                if ($withContext) {
                    $files[] = new ComponentFile($componentType, $componentName, $foundFile);
                } else {
                    $files[] = $foundFile;
                }
            }
        }
        return $files;
    }
}
