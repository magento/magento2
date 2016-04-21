<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\File;

use Magento\Framework\App\Filesystem\DirectoryList;
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
            $this->tmpDirectory->writeFile($filePath, $contents);
        }
        return $this->tmpDirectory->getAbsolutePath($filePath);
    }
}
