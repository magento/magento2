<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\State;

use Magento\Framework\Filesystem;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * A service for cleaning up application state
 */
class CleanupFiles
{
    /**
     * File system
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Clears all files that are subject of code generation
     *
     * @return string[]
     */
    public function clearCodeGeneratedFiles()
    {
        return array_merge(
            $this->clearCodeGeneratedClasses(),
            $this->clearMaterializedViewFiles()
        );
    }

    /**
     * Clears code-generated classes
     *
     * @return string[]
     */
    public function clearCodeGeneratedClasses()
    {
        return array_merge($this->emptyDir(DirectoryList::GENERATION), $this->emptyDir(DirectoryList::DI));
    }

    /**
     * Clears materialized static view files
     *
     * @return string[]
     */
    public function clearMaterializedViewFiles()
    {
        return array_merge(
            $this->emptyDir(DirectoryList::STATIC_VIEW),
            $this->emptyDir(DirectoryList::VAR_DIR, DirectoryList::TMP_MATERIALIZATION_DIR)
        );
    }

    /**
     * Clears all files
     *
     * @return string[]
     */
    public function clearAllFiles()
    {
        return array_merge(
            $this->emptyDir(DirectoryList::STATIC_VIEW),
            $this->emptyDir(DirectoryList::VAR_DIR)
        );
    }

    /**
     * Deletes contents of specified directory
     *
     * @param string $code
     * @param string|null $subPath
     * @return string[]
     */
    private function emptyDir($code, $subPath = null)
    {
        $messages = [];

        $dir = $this->filesystem->getDirectoryWrite($code);
        $dirPath = $dir->getAbsolutePath();
        if (!$dir->isExist()) {
            $messages[] = "The directory '{$dirPath}' doesn't exist - skipping cleanup";
            return $messages;
        }
        foreach ($dir->search('*', $subPath) as $path) {
            if ($path !== '.' && $path !== '..') {
                $messages[] = $dirPath . $path;
                try {
                    $dir->delete($path);
                } catch (FilesystemException $e) {
                    $messages[] = $e->getMessage();
                }
            }
        }

        return $messages;
    }
}
