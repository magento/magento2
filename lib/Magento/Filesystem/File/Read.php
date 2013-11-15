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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Filesystem\File;

use Magento\Filesystem\FilesystemException;

class Read implements ReadInterface
{
    /**
     * Full path to file
     *
     * @var string
     */
    protected $path;

    /**
     * Mode to open the file
     *
     * @var string
     */
    protected $mode = 'r';

    /**
     * Opened file resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->open();
    }

    /**
     * Open file
     *
     * @throws FilesystemException
     */
    protected function open()
    {
        $this->assertValid();
        $this->resource = fopen($this->path, $this->mode);
        if ($this->resource === false) {
            throw new FilesystemException(sprintf('The file "%s" cannot be opened', $this->path));
        }
    }

    /**
     * Assert file existence
     *
     * @throws FilesystemException
     */
    protected function assertValid()
    {
        clearstatcache();

        if (!file_exists($this->path)) {
            throw new FilesystemException(sprintf('The file "%s" doesn\'t exist', $this->path));
        }
    }

    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param int $length The number of bytes to read
     * @return string
     */
    public function read($length)
    {
        return fread($this->resource, $length);
    }

    /**
     * Reads one CSV row from the file
     *
     * @param int $length [optional]
     * @param string $delimiter [optional]
     * @param string $enclosure [optional]
     * @param string $escape [optional]
     * @return array|bool|null
     */
    public function readCsv($length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        return fgetcsv($this->resource, $length, $delimiter, $enclosure, $escape);
    }

    /**
     * Returns the current position
     *
     * @return int
     */
    public function tell()
    {
        return ftell($this->resource);
    }

    /**
     * Seeks to the specified offset
     *
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return fseek($this->resource, $offset, $whence);
    }

    /**
     * Checks if the current position is the end-of-file
     *
     * @return bool
     */
    public function eof()
    {
        return feof($this->resource);
    }

    /**
     * Closes the file.
     *
     * @return bool
     */
    public function close()
    {
        return fclose($this->resource);
    }
}