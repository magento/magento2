<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * Deployment configuration writer to files: env.php, config.php (config.local.php, config.dist.php)
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
     * @param string $pool
     * @param array $comments
     * @return void
     * @throws FileSystemException
     */
    public function saveConfig(array $data, $override = false, $pool = null, array $comments = [])
    {
        foreach ($data as $fileKey => $config) {
            $paths = $pool ? $this->configFilePool->getPathsByPool($pool) : $this->configFilePool->getPaths();

            if (isset($paths[$fileKey])) {
                $currentData = $this->reader->loadConfigFile($fileKey, $paths[$fileKey], true);
                if ($currentData) {
                    if ($override) {
                        $config = array_merge($currentData, $config);
                    } else {
                        $config = array_replace_recursive($currentData, $config);
                    }
                }

                $contents = $this->formatter->format($config, $comments);
                try {
                    $writeFilePath = $paths[$fileKey];
                    $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile($writeFilePath, $contents);
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
