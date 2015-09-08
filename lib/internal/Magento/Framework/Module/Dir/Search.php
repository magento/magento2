<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Dir;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem;

/**
 * Search for files in all module directories
 */
class Search
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $registrar;

    /**
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
     * Search for files in each module by pattern, returns absolute paths
     *
     * @param string $pattern
     * @return array
     */
    public function collectFiles($pattern, $associative = false)
    {
        $files = [];
        foreach ($this->registrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $path) {
            $directoryRead = $this->readFactory->create($path);
            $foundFiles = $directoryRead->search($pattern);
            foreach ($foundFiles as $foundFile) {
                $foundFile = $directoryRead->getAbsolutePath($foundFile);
                if ($associative) {
                    $files[$moduleName][] = $foundFile;
                } else {
                    $files[] = $foundFile;
                }
            }
        }
        return $files;
    }
}
