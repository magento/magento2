<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\State;

use Magento\Framework\Filesystem;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * A service for cleaning up application state
 * @since 2.0.0
 */
class CleanupFiles
{
    /**
     * File system
     *
     * @var Filesystem
     * @since 2.0.0
     */
    private $filesystem;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @since 2.0.0
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Clears all files that are subject of code generation
     *
     * @return string[]
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function clearCodeGeneratedClasses()
    {
        return array_merge(
            $this->emptyDir(DirectoryList::GENERATED_CODE),
            $this->emptyDir(DirectoryList::GENERATED_METADATA)
        );
    }

    /**
     * Clears materialized static view files
     *
     * @return string[]
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
