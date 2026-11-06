<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Phrase;

/**
 * Deployment configuration writer to files: env.php, config.php.
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
     * Config formatter instance.
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
     * The parser of comments from configuration files.
     *
     * @var CommentParser
     */
    private $commentParser;

    /**
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param ConfigFilePool $configFilePool
     * @param DeploymentConfig $deploymentConfig
     * @param Writer\FormatterInterface|null $formatter
     * @param CommentParser|null $commentParser The parser of comments from configuration files
     */
    public function __construct(
        Reader $reader,
        Filesystem $filesystem,
        ConfigFilePool $configFilePool,
        DeploymentConfig $deploymentConfig,
        ?Writer\FormatterInterface $formatter = null,
        ?CommentParser $commentParser = null
    ) {
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->configFilePool = $configFilePool;
        $this->deploymentConfig = $deploymentConfig;
        $this->formatter = $formatter ?: new Writer\PhpFormatter();
        $this->commentParser = $commentParser ?: new CommentParser($filesystem, $configFilePool);
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
     * $pool option is deprecated since version 2.2.0.
     *
     * Usage:
     * ```php
     * saveConfig(
     *      [
     *          ConfigFilePool::APP_ENV => ['some' => 'value'],
     *      ],
     *      true,
     *      null,
     *      [],
     *      false
     * )
     * ```
     *
     * @param array $data The data to be saved
     * @param bool $override Whether values should be overridden
     * @param string $pool The file pool (deprecated)
     * @param array $comments The array of comments
     * @param bool $lock Whether the file should be locked while writing
     * @return void
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveConfig(array $data, $override = false, $pool = null, array $comments = [], bool $lock = false)
    {
        $inputComments = $comments;
        foreach ($data as $fileKey => $config) {
            $paths = $this->configFilePool->getPaths();

            if (!isset($paths[$fileKey])) {
                continue;
            }

            $path = $paths[$fileKey];
            $config = $this->mergeConfigData($fileKey, $config, (bool)$override);
            $commentsToWrite = $this->mergeComments($this->commentParser->execute($path), $inputComments);
            $contents = $this->formatter->format($config, $commentsToWrite);

            try {
                $this->writeConfigFile($path, $contents, $lock);
            } catch (FileSystemException|ValidatorException $e) {
                throw new FileSystemException(
                    new Phrase('The "%1" deployment config file isn\'t writable.', [$path])
                );
            }

            if (function_exists('opcache_invalidate')) {
                opcache_invalidate(
                    $this->filesystem->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath($path)
                );
            }
        }
        $this->deploymentConfig->resetData();
    }

    /**
     * Merge existing file config with incoming config.
     *
     * @param string $fileKey
     * @param array $config
     * @param bool $override
     * @return array
     */
    private function mergeConfigData(string $fileKey, array $config, bool $override): array
    {
        $currentData = $this->reader->load($fileKey);
        if (!$currentData) {
            return $config;
        }

        if ($override) {
            foreach ($currentData as $key => $value) {
                if (!array_key_exists($key, $config)) {
                    $config[$key] = $value;
                }
            }

            return $config;
        }

        return array_replace_recursive($currentData, $config);
    }

    /**
     * Merge comments preserving incoming comment precedence.
     *
     * @param array $existingComments
     * @param array $incomingComments
     * @return array
     */
    private function mergeComments(array $existingComments, array $incomingComments): array
    {
        foreach ($incomingComments as $key => $value) {
            $existingComments[$key] = $value;
        }

        return $existingComments;
    }

    /**
     * Write config contents to target file.
     *
     * @param string $writeFilePath
     * @param string $contents
     * @param bool $lock
     * @return void
     * @throws FileSystemException
     * @throws ValidatorException
     */
    private function writeConfigFile(string $writeFilePath, $contents, bool $lock): void
    {
        $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG);
        if (!$directoryWrite instanceof Write) {
            $directoryWrite->writeFile($writeFilePath, $contents);

            return;
        }

        $targetPermissions = null;
        if ($directoryWrite->isExist($writeFilePath)) {
            $fileStats = $directoryWrite->stat($writeFilePath);
            if (isset($fileStats['mode'])) {
                $targetPermissions = $fileStats['mode'] & 0777;
            }
        }

        $tmpFilePath = $this->getTemporaryPath($writeFilePath);
        try {
            $directoryWrite->writeFile($tmpFilePath, $contents, 'w+', $lock);
            if ($targetPermissions !== null) {
                $directoryWrite->changePermissions($tmpFilePath, $targetPermissions);
            }
            $directoryWrite->renameFile($tmpFilePath, $writeFilePath);
        } catch (FileSystemException|ValidatorException $e) {
            if ($directoryWrite->isExist($tmpFilePath)) {
                try {
                    $directoryWrite->delete($tmpFilePath);
                } catch (\Exception $cleanupException) {
                    unset($cleanupException);
                }
            }

            throw $e;
        }
    }

    /**
     * Build temporary file path in the same directory for atomic write+rename.
     *
     * @param string $path
     * @return string
     */
    private function getTemporaryPath(string $path): string
    {
        $directory = dirname($path);
        $fileName = basename($path);
        $suffix = str_replace('.', '', uniqid('', true));
        $temporaryFileName = sprintf('%s.%s.tmp', $fileName, $suffix);

        if ($directory === '.' || $directory === '') {
            return $temporaryFileName;
        }

        return $directory . '/' . $temporaryFileName;
    }
}
