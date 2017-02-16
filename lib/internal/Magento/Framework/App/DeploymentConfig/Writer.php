<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;

/**
 * Deployment configuration writer to files: env.php, config.php.
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
     * Deployment file config reader
     *
     * @var FileReader
     */
    private $fileReader;

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
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param ConfigFilePool $configFilePool
     * @param DeploymentConfig $deploymentConfig
     * @param Writer\FormatterInterface $formatter
     * @param FileReader $fileReader
     */
    public function __construct(
        Reader $reader,
        Filesystem $filesystem,
        ConfigFilePool $configFilePool,
        DeploymentConfig $deploymentConfig,
        Writer\FormatterInterface $formatter = null,
        FileReader $fileReader = null
    ) {
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->configFilePool = $configFilePool;
        $this->deploymentConfig = $deploymentConfig;
        $this->formatter = $formatter ?: ObjectManager::getInstance()->get(Writer\PhpFormatter::class);
        $this->fileReader = $fileReader ?: ObjectManager::getInstance()->get(FileReader::class);
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
     * Saves config in specified file.
     * Usage:
     * ```php
     * saveConfig(
     *      [
     *          ConfigFilePool::APP_ENV => ['some' => 'value'],
     *      ],
     *      true,
     *      null,
     *      []
     * )
     * ```
     *
     * @param array $data The data to be saved
     * @param bool $override Whether values should be overrided
     * @param string $pool The file pool (deprecated)
     * @param array $comments The array of comments
     * @return void
     * @throws FileSystemException
     */
    public function saveConfig(array $data, $override = false, $pool = null, array $comments = [])
    {
        $paths = $this->configFilePool->getPaths();

        foreach ($data as $fileKey => $config) {
            if (isset($paths[$fileKey])) {
                $currentData = $this->fileReader->load($fileKey);
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
