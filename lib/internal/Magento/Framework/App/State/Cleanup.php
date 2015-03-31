<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\State;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Source;

/**
 * A service for cleaning up application state
 */
class Cleanup
{
    /**
     * Cache frontend pool
     *
     * @var Pool
     */
    private $cachePool;

    /**
     * File system
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param Pool $cachePool
     * @param Filesystem $filesystem
     */
    public function __construct(Pool $cachePool, Filesystem $filesystem)
    {
        $this->cachePool = $cachePool;
        $this->filesystem = $filesystem;
    }

    /**
     * Clears all caches
     *
     * @return void
     */
    public function clearCaches()
    {
        /** @var \Magento\Framework\Cache\FrontendInterface $frontend */
        foreach ($this->cachePool as $frontend) {
            $frontend->clean();
        }
    }

    /**
     * Clears all files that are subject of code generation
     *
     * @return void
     */
    public function clearCodeGeneratedFiles()
    {
        $this->clearCodeGeneratedClasses();
        $this->clearMaterializedViewFiles();
    }

    /**
     * Clears code-generated classes
     *
     * @return void
     */
    public function clearCodeGeneratedClasses()
    {
        $this->emptyDir(DirectoryList::GENERATION);
    }

    /**
     * Clears materialized static view files
     *
     * @return void
     */
    public function clearMaterializedViewFiles()
    {
        $this->emptyDir(DirectoryList::STATIC_VIEW);
        $this->emptyDir(DirectoryList::VAR_DIR, DirectoryList::TMP_MATERIALIZATION_DIR);
    }

    /**
     * Deletes contents of specified directory
     *
     * @param string $code
     * @param string|null $subPath
     * @return void
     */
    private function emptyDir($code, $subPath = null)
    {
        $dir = $this->filesystem->getDirectoryWrite($code);
        foreach ($dir->search('*', $subPath) as $path) {
            if (false === strpos($path, '.')) {
                $dir->delete($path);
            }
        }
    }
}
