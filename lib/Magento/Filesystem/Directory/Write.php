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

class Write extends Read implements WriteInterface
{
    /**
     * @var int
     */
    protected $permissions;

    /**
     * @param string $path
     * @param \Magento\Filesystem\File\WriteFactory $fileFactory
     * @param $permissions
     */
    public function __construct($path, \Magento\Filesystem\File\WriteFactory $fileFactory, $permissions)
    {
        $this->path = rtrim($path, '/') . '/';
        $this->fileFactory = $fileFactory;
        $this->permissions = $permissions;
    }

    /**
     * Check it directory is writable
     *
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function assertWritable($path)
    {
        clearstatcache();
        $absolutePath = $this->getAbsolutePath($path);
        if (is_writable($absolutePath) === false) {
            throw new FilesystemException(sprintf('The path "%s" is not writable', $absolutePath));
        }
    }

    /**
     * Recursively asserts parent folder are either not exists or exists and have write permissions
     *
     * @param string $absolutePath
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function assertParentsWritable($absolutePath)
    {
        clearstatcache();
        if (!is_writable($absolutePath)) {
            if (file_exists($absolutePath)) {
                throw new FilesystemException(sprintf('The path "%s" is not writable', $absolutePath));
            } else {
                $this->assertParentsWritable(dirname($absolutePath));
            }
        }
    }

    /**
     * Create directory if it does not exists
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function create($path)
    {
        clearstatcache();
        $absolutePath = $this->getAbsolutePath($path);
        if (is_dir($absolutePath)) {
            return true;
        } elseif (is_file($absolutePath)) {
            throw new FilesystemException(sprintf('The "%s" file already exists', $absolutePath));
        }
        $this->assertParentsWritable($absolutePath);

        $result = mkdir($absolutePath, $this->permissions, true);
        if ($result === false) {
            throw new FilesystemException(sprintf('Directory "%s" cannot be created', $absolutePath));
        }
        return $result;
    }

    /**
     * Renames a source to into new name
     *
     * @param string $path
     * @param string $newPath
     * @param WriteInterface $targetDirectory
     * @return bool
     * @throws FilesystemException
     */
    public function rename($path, $newPath, WriteInterface $targetDirectory = null)
    {
        $this->assertExist($path);

        $targetDirectory = $targetDirectory ? : $this;
        if (!$targetDirectory->isExist(dirname($newPath))) {
            $targetDirectory->create(dirname($newPath));
        }

        $absolutePath = $this->getAbsolutePath($path);
        $absoluteNewPath = $targetDirectory->getAbsolutePath($newPath);

        $result = rename($absolutePath, $absoluteNewPath);
        if ($result === null) {
            throw new FilesystemException(
                sprintf('The "%s" path cannot be renamed into "%s"', $absolutePath, $absoluteNewPath)
            );
        }
        return $result;
    }

    /**
     * Copy a source to into destination
     *
     * @param string $path
     * @param string $destination
     * @param WriteInterface $targetDirectory
     * @return bool
     * @throws FilesystemException
     */
    public function copy($path, $destination, WriteInterface $targetDirectory = null)
    {
        $this->assertExist($path);

        $targetDirectory = $targetDirectory ? : $this;
        if (!$targetDirectory->isExist(dirname($destination))) {
            $targetDirectory->create(dirname($destination));
        }

        $absolutePath = $this->getAbsolutePath($path);
        $absoluteDestinationPath = $targetDirectory->getAbsolutePath($destination);

        $result = copy($absolutePath, $absoluteDestinationPath);
        if ($result === null) {
            throw new FilesystemException(
                sprintf('The "%s" path cannot be renamed into "%s"', $absolutePath, $absoluteDestinationPath)
            );
        }
        return $result;
    }

    /**
     * Delete given path
     *
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function delete($path = null)
    {
        $this->assertExist($path);

        $absolutePath = $this->getAbsolutePath($path);
        if (is_file($absolutePath)) {
            $result = unlink($this->getAbsolutePath($path));
        } else {
            foreach ($this->read($path) as $subPath) {
                $this->delete($subPath);
            }
            $result = rmdir($absolutePath);
        }
        if ($result === false) {
            throw new FilesystemException(sprintf('The file or directory "%s" cannot be deleted', $absolutePath));
        }
        return $result;
    }

    /**
     * Change permissions of given path
     *
     * @param string $path
     * @param int $permissions
     * @return bool
     * @throws FilesystemException
     */
    public function changePermissions($path, $permissions)
    {
        $this->assertExist($path);

        $absolutePath = $this->getAbsolutePath($path);
        $result = chmod($absolutePath, $permissions);
        if ($result === false) {
            throw new FilesystemException(sprintf('Cannot change permissions for "%s" path', $absolutePath));
        }
        return $result;
    }

    /**
     * Sets access and modification time of file.
     *
     * @param string $path
     * @param int|null $modificationTime
     * @return bool
     * @throws FilesystemException
     */
    public function touch($path, $modificationTime = null)
    {
        $absolutePath = $this->getAbsolutePath($path);

        $folder = dirname($path);
        $this->create($folder);
        $this->assertWritable($folder);

        if ($modificationTime === null) {
            $result = touch($absolutePath);
        } else {
            $result = touch($absolutePath, $modificationTime);
        }
        if ($result === false) {
            throw new FilesystemException(sprintf('The file or directory "%s" cannot be touched', $absolutePath));
        }
        return $result;
    }

    /**
     * Check if given path is writable
     *
     * @param string|null $path
     * @return bool
     */
    public function isWritable($path = null)
    {
        clearstatcache();

        return is_writable($this->getAbsolutePath($path));
    }

    /**
     * Open file in given mode
     *
     * @param string $path
     * @param string $mode
     * @return \Magento\Filesystem\File\WriteInterface
     */
    public function openFile($path, $mode = 'w')
    {
        $absolutePath = $this->getAbsolutePath($path);

        $folder = dirname($path);
        $this->create($folder);
        $this->assertWritable($folder);

        return $this->fileFactory->create($absolutePath, $mode);
    }

    /**
     * Open file in given path
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @return int The number of bytes that were written.
     * @throws FilesystemException
     */
    public function writeFile($path, $content, $mode = null)
    {
        $absolutePath = $this->getAbsolutePath($path);

        $folder = dirname($path);
        $this->create($folder);
        $this->assertWritable($folder);

        $result = file_put_contents($absolutePath, $content, $mode);
        if ($result === null) {
            throw new FilesystemException(sprintf('The specified "%s" file could not be written', $absolutePath));
        }
        return $result;
    }
}