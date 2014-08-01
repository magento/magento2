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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Directory;

use Magento\Filesystem\Driver\DriverInterface;
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
     * Filesystem driver
     *
     * @var \Magento\Filesystem\Driver\DriverInterface
     */
    protected $driver;

    /**
     * @param DriverInterface $driver
     * @param array $config
     */
    public function __construct(
        DriverInterface $driver,
        array $config
    ) {
        $this->driver = $driver;
        $this->setProperties($config);
    }

    /**
     * Set properties from config
     *
     * @param array $config
     * @return void
     * @throws FilesystemException
     */
    protected function setProperties(array $config)
    {
        if (!empty($config['path'])) {
            $this->path = rtrim(str_replace('\\', '/', $config['path']), '/') . '/';
        }
    }

    /**
     * Retrieves absolute path
     * E.g.: /var/www/application/file.txt
     *
     * @param string $path
     * @return string
     */
    public function getAbsolutePath($path = null)
    {
        return $this->driver->getAbsolutePath($this->path, $path);
    }

    /**
     * Check a file or directory exists
     *
     * @param string $path [optional]
     * @return bool
     * @throws FilesystemException
     */
    public function isExist($path = null)
    {
        return $this->driver->isExists($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Check permissions for reading file or directory
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function isReadable($path = null)
    {
        return $this->driver->isReadable($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Check whether given path is directory
     *
     * @param string $path
     * @return bool
     */
    public function isDirectory($path = null)
    {
        return $this->driver->isDirectory($this->driver->getAbsolutePath($this->path, $path));
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws FilesystemException
     */
    public function readFile($path, $flag = null, $context = null)
    {
        clearstatcache();
        $result = @file_get_contents($this->getAbsolutePath($path), $flag, $context);
        if (false === $result) {
            throw new FilesystemException(
                sprintf('Cannot read contents from file "%s"', $path)
            );
        }
        return $result;
    }
}
