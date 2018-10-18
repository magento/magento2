<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\File\ReadFactoryInterface;

class Read implements ReadInterface
{
    /**
     * Directory path
     *
     * @var string
     */
    protected $path;

    /**
     * File factory
     *
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $fileFactory;

    /**
     * Filesystem driver
     *
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $driver;

    /**
     * @var PathValidatorInterface
     */
    private $pathValidator;

    /**
     * Constructor. Set properties.
     *
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileFactory
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     * @param string $path
     * @param PathValidatorInterface|null $pathValidator
     */
    public function __construct(
        \Magento\Framework\Filesystem\File\ReadFactory $fileFactory,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        $path,
        PathValidatorInterface $pathValidator = null
    ) {
        $this->fileFactory = $fileFactory;
        $this->driver = $driver;
        $this->setPath($path);
        $this->pathValidator = $pathValidator;
    }

    /**
     * @param string|null $path
     * @param string|null $scheme
     * @param bool $absolutePath
     *
     * @return void
     * @throws ValidatorException
     */
    protected function validatePath(
        $path = null,
        $scheme = null,
        $absolutePath = false
    ) {
        if ($path && $this->pathValidator) {
            $this->pathValidator->validate(
                $this->path,
                $path,
                $scheme,
                $absolutePath
            );
        }
    }

    /**
     * Sets base path
     *
     * @param string $path
     * @return void
     */
    protected function setPath($path)
    {
        if (!empty($path)) {
            $this->path = rtrim(str_replace('\\', '/', $path), '/') . '/';
        }
    }

    /**
     * Retrieves absolute path
     * E.g.: /var/www/application/file.txt
     *
     * @param string $path
     * @param string $scheme
     * @return string
     * @throws ValidatorException
     */
    public function getAbsolutePath($path = null, $scheme = null)
    {
        $this->validatePath($path, $scheme);

        return $this->driver->getAbsolutePath($this->path, $path, $scheme);
    }

    /**
     * Retrieves relative path
     *
     * @param string $path
     * @return string
     * @throws ValidatorException
     */
    public function getRelativePath($path = null)
    {
        $this->validatePath(
            $path,
            null,
            $path && $path[0] === DIRECTORY_SEPARATOR
        );

        return $this->driver->getRelativePath($this->path, $path);
    }

    /**
     * Retrieve list of all entities in given path
     *
     * @param string|null $path
     * @return string[]
     * @throws ValidatorException
     */
    public function read($path = null)
    {
        $this->validatePath($path);

        $files = $this->driver->readDirectory($this->driver->getAbsolutePath($this->path, $path));
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->getRelativePath($file);
        }

        return $result;
    }

    /**
     * Read recursively
     *
     * @param null $path
     * @return string[]
     * @throws ValidatorException
     */
    public function readRecursively($path = null)
    {
        $this->validatePath($path);

        $result = [];
        $paths = $this->driver->readDirectoryRecursively($this->driver->getAbsolutePath($this->path, $path));
        /** @var \FilesystemIterator $file */
        foreach ($paths as $file) {
            $result[] = $this->getRelativePath($file);
        }
        sort($result);

        return $result;
    }

    /**
     * Search all entries for given regex pattern
     *
     * @param string $pattern
     * @param string $path [optional]
     * @return string[]
     * @throws ValidatorException
     */
    public function search($pattern, $path = null)
    {
        $this->validatePath($path);

        if ($path) {
            $absolutePath = $this->driver->getAbsolutePath($this->path, $this->getRelativePath($path));
        } else {
            $absolutePath = $this->path;
        }

        $files = $this->driver->search($pattern, $absolutePath);
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->getRelativePath($file);
        }

        return $result;
    }

    /**
     * Check a file or directory exists
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function isExist($path = null)
    {
        $this->validatePath($path);

        return $this->driver->isExists($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function stat($path)
    {
        $this->validatePath($path);

        return $this->driver->stat($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws ValidatorException
     */
    public function isReadable($path = null)
    {
        $this->validatePath($path);

        return $this->driver->isReadable($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Open file in read mode
     *
     * @param string $path
     *
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @throws ValidatorException
     */
    public function openFile($path)
    {
        $this->validatePath($path);

        return $this->fileFactory->create(
            $this->driver->getAbsolutePath($this->path, $path),
            $this->driver
        );
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function readFile($path, $flag = null, $context = null)
    {
        $this->validatePath($path);

        $absolutePath = $this->driver->getAbsolutePath($this->path, $path);

        return $this->driver->fileGetContents($absolutePath, $flag, $context);
    }

    /**
     * Check whether given path is file
     *
     * @param string $path
     * @return bool
     * @throws ValidatorException
     */
    public function isFile($path)
    {
        $this->validatePath($path);

        return $this->driver->isFile($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Check whether given path is directory
     *
     * @param string $path [optional]
     * @return bool
     * @throws ValidatorException
     */
    public function isDirectory($path = null)
    {
        $this->validatePath($path);

        return $this->driver->isDirectory($this->driver->getAbsolutePath($this->path, $path));
    }
}
