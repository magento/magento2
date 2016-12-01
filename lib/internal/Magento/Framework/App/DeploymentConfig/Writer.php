<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Phrase;

/**
 * Deployment configuration writer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Writer
{
    /**
     * Deployment config reader
     *
     * @var Reader
     */
    private $reader;

    /**
     * Application filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Formatter
     *
     * @var Writer\FormatterInterface
     */
    private $formatter;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Constructor
     *
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param ConfigFilePool $configFilePool
     * @param DeploymentConfig $deploymentConfig
     * @param Writer\FormatterInterface $formatter
     */
    public function __construct(
        Reader $reader,
        Filesystem $filesystem,
        ConfigFilePool $configFilePool,
        DeploymentConfig $deploymentConfig,
        Writer\FormatterInterface $formatter = null
    ) {
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->formatter = $formatter ?: new Writer\PhpFormatter();
        $this->configFilePool = $configFilePool;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Check if configuration file is writable
     *
     * @return bool
     */
    public function checkIfWritable()
    {
        $configDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG);
        foreach ($this->reader->getFiles() as $file) {
            if (!$configDirectory->isWritable($file)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Saves config
     *
     * @param array $data
     * @param bool $override
     * @return void
     */
    public function saveConfig(array $data, $override = false)
    {
        $paths = $this->configFilePool->getPaths();

        foreach ($data as $fileKey => $config) {
            if (isset($paths[$fileKey])) {
                if ($this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->isExist($paths[$fileKey])) {
                    $currentData = $this->reader->load($fileKey);
                    if ($override) {
                        $config = array_merge($currentData, $config);
                    } else {
                        $config = array_replace_recursive($currentData, $config);
                    }
                }

                $contents = $this->formatter->format($config);
                try {
                    $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile($paths[$fileKey], $contents);
                } catch (FileSystemException $e) {
                    throw new FileSystemException(
                        new Phrase('Deployment config file %1 is not writable.', [$paths[$fileKey]])
                    );
                }
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate(
                        $this->filesystem->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath($paths[$fileKey])
                    );
                }
            }
        }
        $this->deploymentConfig->resetData();
    }
}
