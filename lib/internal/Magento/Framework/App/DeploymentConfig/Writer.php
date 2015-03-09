<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Deployment configuration writer
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
     * Constructor
     *
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param ConfigFilePool $configFilePool
     * @param Writer\FormatterInterface $formatter
     */
    public function __construct(
        Reader $reader,
        Filesystem $filesystem,
        ConfigFilePool $configFilePool,
        Writer\FormatterInterface $formatter = null
    ) {
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->formatter = $formatter ?: new Writer\PhpFormatter();
        $this->configFilePool = $configFilePool;
    }

    /**
     * Creates the deployment configuration file
     *
     * Will overwrite a file, if it exists.
     *
     * @param SegmentInterface[] $segments
     * @return void
     * @throws \InvalidArgumentException
     */
    public function create($segments)
    {
        $data = [];
        foreach ($segments as $segment) {
            if (!($segment instanceof SegmentInterface)) {
                throw new \InvalidArgumentException('An instance of SegmentInterface is expected.');
            }
            $data[$segment->getKey()] = $segment->getData();
        }
        $this->write($data);
    }

    /**
     * Update data in the configuration file using specified segment object
     *
     * @param SegmentInterface $segment
     * @return void
     */
    public function update(SegmentInterface $segment)
    {
        $key = $segment->getKey();
        $data = $this->reader->load();
        $data[$key] = $segment->getData();
        $this->write($data);
    }

    /**
     * Check if configuration file is writable
     *
     * @return bool
     */
    public function checkIfWritable()
    {
        $configDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG);
        if ($configDirectory->isWritable($this->reader->getFile())) {
            return true;
        }
        return false;
    }

    /**
     * Saves config
     *
     * @param array $data
     * @return void
     */
    public function saveConfig(array $data)
    {
        $paths = $this->configFilePool->getPaths();

        foreach ($data as $fileKey => $config) {
            if (isset($paths[$fileKey])) {
                $contents = $this->formatter->format($config);
                $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile($paths[$fileKey], $contents);
            }
        }
    }

    /**
     * Persists the data into file
     *
     * @param array $data
     * @return void
     */
    private function write($data)
    {
        $contents = $this->formatter->format($data);
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile($this->reader->getFile(), $contents);
    }
}
