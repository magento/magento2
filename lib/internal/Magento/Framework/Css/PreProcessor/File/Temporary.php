<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\PreProcessor\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Css\PreProcessor\Config;

class Temporary
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $tmpDirectory;

    /**
     * @param Filesystem $filesystem
     * @param Config $config
     */
    public function __construct(
        Filesystem $filesystem,
        Config $config
    ) {
        $this->tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->config = $config;
    }

    /**
     * Write down contents to a temporary file and return its absolute path
     *
     * @param string $relativePath
     * @param string $contents
     * @return string
     */
    public function createFile($relativePath, $contents)
    {
        $filePath =  $this->config->getMaterializationRelativePath() . '/' . $relativePath;

        if (!$this->tmpDirectory->isExist($filePath)) {
            /**
             * Materialise through a process-unique file and rename it into place. Rename is atomic
             * on *nix, while writing in place is not: writeFile() opens with 'w+', so it truncates
             * before it writes and another process reading the same import - stylesheets in a
             * package share their partials - could parse an empty or half-written file. The same
             * approach is used when generating code, see Code\Generator\Io::writeResultFile().
             */
            $temporaryPath = $filePath . '.' . getmypid();
            $this->tmpDirectory->writeFile($temporaryPath, $contents);
            try {
                $this->tmpDirectory->renameFile($temporaryPath, $filePath);
            } catch (FileSystemException $e) {
                // Another process may have materialised the same file first, which is fine as long
                // as the file is now there; otherwise the failure is real.
                if (!$this->tmpDirectory->isExist($filePath)) {
                    throw $e;
                }
                $this->tmpDirectory->delete($temporaryPath);
            }
        }
        return $this->tmpDirectory->getAbsolutePath($filePath);
    }
}
