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
use Magento\Framework\Filesystem\DriverPool;

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
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $directoryReadFactory;

    /**
     * @param ScopeConfigInterface $config
     * @param Filesystem $filesystem
     * @param Filesystem\Directory\ReadFactory|null $directoryReadFactory
     */
    public function __construct(
        ScopeConfigInterface $config,
        Filesystem $filesystem,
        Filesystem\Directory\ReadFactory $directoryReadFactory
    ) {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->directoryReadFactory = $directoryReadFactory;
    }

    /**
     * Directory that users are allowed to place images for importing.
     *
     * @return ReadInterface
     */
    public function getDirectory(): ReadInterface
    {
        $path = $this->getDirectoryRelativePath();

        return $this->getDirectoryReadByPath(
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

    /**
     * Create an instance of directory with read permissions by path.
     *
     * @param string $path
     * @param string $driverCode
     *
     * @return ReadInterface
     */
    private function getDirectoryReadByPath(string $path, string $driverCode = DriverPool::FILE): ReadInterface
    {
        return $this->directoryReadFactory->create($path, $driverCode);
    }
}
