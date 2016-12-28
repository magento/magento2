<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Backup\Filesystem\Iterator\Filter;
use Magento\Framework\Filesystem\Filter\ExcludeFilter;
use Magento\Framework\Filesystem;

class FilePermissions
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * List of required writable directories for installation
     *
     * @var array
     */
    protected $installationWritableDirectories = [];

    /**
     * List of recommended non-writable directories for application
     *
     * @var array
     */
    protected $applicationNonWritableDirectories = [];

    /**
     * List of current writable directories for installation
     *
     * @var array
     */
    protected $installationCurrentWritableDirectories = [];

    /**
     * List of current non-writable directories for application
     *
     * @var array
     */
    protected $applicationCurrentNonWritableDirectories = [];

    /**
     * List of non-writable paths in a specified directory
     *
     * @var array
     */
    protected $nonWritablePathsInDirectories = [];

    /**
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Filesystem $filesystem,
        DirectoryList $directoryList
    ) {
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
    }

    /**
     * Retrieve list of required writable directories for installation
     *
     * @return array
     */
    public function getInstallationWritableDirectories()
    {
        if (!$this->installationWritableDirectories) {
            $data = [
                DirectoryList::CONFIG,
                DirectoryList::VAR_DIR,
                DirectoryList::MEDIA,
                DirectoryList::STATIC_VIEW,
            ];
            foreach ($data as $code) {
                $this->installationWritableDirectories[$code] = $this->directoryList->getPath($code);
            }
        }
        return array_values($this->installationWritableDirectories);
    }

    /**
     * Retrieve list of recommended non-writable directories for application
     *
     * @return array
     */
    public function getApplicationNonWritableDirectories()
    {
        if (!$this->applicationNonWritableDirectories) {
            $data = [
                DirectoryList::CONFIG,
            ];
            foreach ($data as $code) {
                $this->applicationNonWritableDirectories[$code] = $this->directoryList->getPath($code);
            }
        }
        return array_values($this->applicationNonWritableDirectories);
    }

    /**
     * Retrieve list of currently writable directories for installation
     *
     * @return array
     */
    public function getInstallationCurrentWritableDirectories()
    {
        if (!$this->installationCurrentWritableDirectories) {
            foreach ($this->installationWritableDirectories as $code => $path) {
                if ($this->isWritable($code)) {
                    if ($this->checkRecursiveDirectories($path)) {
                        $this->installationCurrentWritableDirectories[] = $path;
                    }
                } else {
                    $this->nonWritablePathsInDirectories[$path] = [$path];
                }
            }
        }
        return $this->installationCurrentWritableDirectories;
    }

    /**
     * Check all sub-directories and files except for generated/code and generated/metadata
     *
     * @param string $directory
     * @return bool
     */
    private function checkRecursiveDirectories($directory)
    {
        $directoryIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        $noWritableFilesFolders = [
            $this->directoryList->getPath(DirectoryList::GENERATION) . '/',
            $this->directoryList->getPath(DirectoryList::DI) . '/',
        ];

        $directoryIterator = new Filter($directoryIterator, $noWritableFilesFolders);

        $directoryIterator = new ExcludeFilter(
            $directoryIterator,
            [
                $this->directoryList->getPath(DirectoryList::SESSION) . '/',
            ]
        );

        $foundNonWritable = false;

        try {
            foreach ($directoryIterator as $subDirectory) {
                if (!$subDirectory->isWritable() && !$subDirectory->isLink()) {
                    $this->nonWritablePathsInDirectories[$directory][] = $subDirectory->getPathname();
                    $foundNonWritable = true;
                }
            }
        } catch (\UnexpectedValueException $e) {
            return false;
        }
        return !$foundNonWritable;
    }

    /**
     * Retrieve list of currently non-writable directories for application
     *
     * @return array
     */
    public function getApplicationCurrentNonWritableDirectories()
    {
        if (!$this->applicationCurrentNonWritableDirectories) {
            foreach ($this->applicationNonWritableDirectories as $code => $path) {
                if ($this->isNonWritable($code)) {
                    $this->applicationCurrentNonWritableDirectories[] = $path;
                }
            }
        }
        return $this->applicationCurrentNonWritableDirectories;
    }

    /**
     * Checks if directory is writable by given directory code
     *
     * @param string $code
     * @return bool
     */
    protected function isWritable($code)
    {
        $directory = $this->filesystem->getDirectoryWrite($code);
        return $this->isReadableDirectory($directory) && $directory->isWritable();
    }

    /**
     * Checks if directory is non-writable by given directory code
     *
     * @param string $code
     * @return bool
     */
    protected function isNonWritable($code)
    {
        $directory = $this->filesystem->getDirectoryWrite($code);
        return $this->isReadableDirectory($directory) && !$directory->isWritable();
    }

    /**
     * Checks if directory exists and is readable
     *
     * @param \Magento\Framework\Filesystem\Directory\WriteInterface $directory
     * @return bool
     */
    protected function isReadableDirectory($directory)
    {
        if (!$directory->isExist() || !$directory->isDirectory() || !$directory->isReadable()) {
            return false;
        }
        return true;
    }

    /**
     * Checks writable paths for installation, returns associative array if input is true, else returns simple array
     *
     * @param bool $associative
     * @return array
     */
    public function getMissingWritablePathsForInstallation($associative = false)
    {
        $required = $this->getInstallationWritableDirectories();
        $current = $this->getInstallationCurrentWritableDirectories();
        $missingPaths = [];
        foreach (array_diff($required, $current) as $missingPath) {
            if (isset($this->nonWritablePathsInDirectories[$missingPath])) {
                if ($associative) {
                    $missingPaths[$missingPath] = $this->nonWritablePathsInDirectories[$missingPath];
                } else {
                    $missingPaths = array_merge(
                        $missingPaths,
                        $this->nonWritablePathsInDirectories[$missingPath]
                    );
                }
            }
        }
        if ($associative) {
            $required = array_flip($required);
            $missingPaths = array_merge($required, $missingPaths);
        }
        return $missingPaths;
    }

    /**
     * Checks writable directories for installation
     *
     * @deprecated Use getMissingWritablePathsForInstallation() to get all missing writable paths required for install
     * @return array
     */
    public function getMissingWritableDirectoriesForInstallation()
    {
        $required = $this->getInstallationWritableDirectories();
        $current = $this->getInstallationCurrentWritableDirectories();
        return array_diff($required, $current);
    }

    /**
     * Checks non-writable directories for application
     *
     * @return array
     */
    public function getUnnecessaryWritableDirectoriesForApplication()
    {
        $required = $this->getApplicationNonWritableDirectories();
        $current = $this->getApplicationCurrentNonWritableDirectories();
        return array_diff($required, $current);
    }
}
