<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ImportExport\Model\Import;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Provides base directory to use for images when user imports entities.
 */
class ImageDirectoryBaseProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param ScopeConfigInterface $config
     * @param Filesystem $filesystem
     */
    public function __construct(ScopeConfigInterface $config, Filesystem $filesystem)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    /**
     * Directory that users are allowed to place images for importing.
     *
     * @return ReadInterface
     */
    public function getDirectory(): ReadInterface
    {
        $path = $this->getDirectoryRelativePath();

        return $this->filesystem->getDirectoryReadByPath(
            $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath($path)
        );
    }

    /**
     * The directory's path relative to Magento root.
     *
     * @return string
     */
    public function getDirectoryRelativePath(): string
    {
        return $this->config->getValue('general/file/import_images_base_dir');
    }
}
