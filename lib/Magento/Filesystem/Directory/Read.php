<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Filesystem\Directory;

use Magento\Filesystem\FilesystemException;

class Read implements ReadInterface
{
    /**
     * Directory path
     *
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * File factory
     *
     * @var \Magento\Filesystem\File\ReadFactory
     */
    protected $fileFactory;

    /**
     * Filesystem driver
     *
     * @var \Magento\Filesystem\DriverInterface
     */
    protected $driver;

    /**
     * Constructor. Set properties.
     *
     * @param array $config
     * @param \Magento\Filesystem\File\ReadFactory $fileFactory
     * @param \Magento\Filesystem\DriverInterface $driver
     */
    public function __construct
    (
        array $config,
        \Magento\Filesystem\File\ReadFactory $fileFactory,
        \Magento\Filesystem\DriverInterface $driver
    ) {
        $this->setProperties($config);
        $this->fileFactory = $fileFactory;
        $this->driver = $driver;
    }

    /**
     * Set properties from config
     *
     * @param array $config
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function setProperties(array $config)
    {
        if (!empty($config['path'])) {
            $this->path = rtrim(str_replace('\\', '/', $config['path']), '/') . '/';
        }

        if (!empty($config['protocol'])) {
            $this->scheme = $config['protocol'];
        }
    }

    /**
     * Retrieves absolute path
     * E.g.: /var/www/application/file.txt
     *
     * @param string $path
     * @param string $schema
     * @return string
     */
    public function getAbsolutePath($path = null, $schema = null)
    {
        return $this->driver->getAbsolutePath($this->path, $path, $schema);
    }

    /**
     * Retrieves relative path
     *
     * @param string $path
     * @return string
     */
    public function getRelativePath($path = null)
    {
        return $this->driver->getRelativePath($this->path, $path);
    }
    /**
     * Validate of path existence
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function assertExist($path)
    {
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);
        if ($this->driver->isExists($absolutePath) === false) {
            throw new FilesystemException(sprintf('The path "%s" doesn\'t exist', $absolutePath));
        }
        return true;
    }

    /**
     * Retrieve list of all entities in given path
     *
     * @param string|null $path
     * @return array
     */
    public function read($path = null)
    {
        $this->assertExist($path);

        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \FilesystemIterator($this->driver->getAbsolutePath($this->path, $path), $flags);
        $result = array();
        /** @var \FilesystemIterator $file */
        foreach ($iterator as $file) {
            $result[] = $this->getRelativePath($file->getPathname());
        }
        sort($result);
        return $result;
    }

    /**
     * Search all entries for given regex pattern
     *
     * @param string $pattern
     * @param string $path [optional]
     * @return array
     */
    public function search($pattern, $path = null)
    {
        clearstatcache();
        if ($path) {
            $absolutePath = $this->driver->getAbsolutePath($this->path, $this->getRelativePath($path));
        } else {
            $absolutePath = $this->path;
        }

        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolutePath, $flags), \RecursiveIteratorIterator::CHILD_FIRST
            ),
            $pattern
        );
        $result = array();
        /** @var \FilesystemIterator $file */
        foreach ($iterator as $file) {
            $result[] = $this->getRelativePath($file->getPathname());
        }
        sort($result);
        return $result;
    }

    /**
     * Check a file or directory exists
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function isExist($path = null)
    {
        return $this->driver->isExists($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function stat($path)
    {
        $this->assertExist($path);
        return $this->driver->stat($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function isReadable($path)
    {
        return $this->driver->isReadable($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Open file in read mode
     *
     * @param string $path
     * @param string|null $protocol
     *
     * @return \Magento\Filesystem\File\ReadInterface
     */
    public function openFile($path, $protocol = null)
    {
        return $this->fileFactory->create($this->driver->getAbsolutePath($this->path, $path), $protocol, $this->driver);
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @param string|null $protocol
     * @return string
     * @throws FilesystemException
     */
    public function readFile($path, $flag = null, $context = null, $protocol = null)
    {
        $absolutePath = $this->driver->getAbsolutePath($this->path, $path, $protocol);

        /** @var \Magento\Filesystem\File\Read $fileReader */
        $fileReader = $this->fileFactory->create($absolutePath, $protocol, $this->driver);
        return $fileReader->readAll($flag, $context);
    }

    /**
     * Check whether given path is file
     *
     * @param string $path
     * @return bool
     */
    public function isFile($path)
    {
        return $this->driver->isFile($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Check whether given path is directory
     *
     * @param string $path
     * @return bool
     */
    public function isDirectory($path)
    {
        return $this->driver->isDirectory($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Checks is directory contains path
     * Utility method.
     *
     * @param string $path
     * @param string $directory
     * @return bool
     */
    public function isPathInDirectory($path, $directory)
    {
        return $this->driver->isPathInDirectory($path, $directory);
    }
}
