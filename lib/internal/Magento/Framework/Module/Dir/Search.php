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
     * @var Filesystem\Directory\ReadInterface
     */
    private $directoryRead;

    public function __construct(ComponentRegistrarInterface $registrar, Filesystem $filesystem)
    {
        $this->registrar = $registrar;
        $this->directoryRead = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    /**
     * Search for files in each module by pattern
     *
     * @param $pattern
     * @return array
     */
    public function collectFiles($pattern)
    {
        $files = [];
        foreach ($this->registrar->getPaths(ComponentRegistrar::MODULE) as $path) {
            $relativePath = $this->directoryRead->getRelativePath($path);
            $files = array_merge($files, $this->directoryRead->search($relativePath . '/' . $pattern));
        }
        return $files;
    }
}
