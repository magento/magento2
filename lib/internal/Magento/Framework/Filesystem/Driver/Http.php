<?php
/**
 * Origin filesystem driver
 *
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
namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\Filesystem\FilesystemException;

/**
 * Class Http
 *
 */
class Http extends File
{
    /**
     * @var string
     */
    protected $scheme = 'http';

    /**
     * @param string $path
     * @return bool
     * @throws FilesystemException
     */
    public function isExists($path)
    {
        $headers = array_change_key_case(get_headers($this->getScheme() . $path, 1), CASE_LOWER);

        $status = $headers[0];

        if (strpos($status, '200 OK') === false) {
            $result = false;
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Gathers the statistics of the given path
     *
     * @param string $path
     * @return array
     */
    public function stat($path)
    {
        $headers = array_change_key_case(get_headers($this->getScheme() . $path, 1), CASE_LOWER);

        $result = array(
            'dev' => 0,
            'ino' => 0,
            'mode' => 0,
            'nlink' => 0,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'atime' => 0,
            'ctime' => 0,
            'blksize' => 0,
            'blocks' => 0,
            'size' => isset($headers['content-length']) ? $headers['content-length'] : 0,
            'type' => isset($headers['content-type']) ? $headers['content-type'] : '',
            'mtime' => isset($headers['last-modified']) ? $headers['last-modified'] : 0,
            'disposition' => isset($headers['content-disposition']) ? $headers['content-disposition'] : null
        );
        return $result;
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flags
     * @param resource|null $context
     * @return string
     * @throws FilesystemException
     */
    public function fileGetContents($path, $flags = null, $context = null)
    {
        clearstatcache();
        $result = @file_get_contents($this->getScheme() . $path, $flags, $context);
        if (false === $result) {
            throw new FilesystemException(
                sprintf('Cannot read contents from file "%s" %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Open file in given path
     *
     * @param string $path
     * @param string $content
     * @param string|null $mode
     * @param resource|null $context
     * @return int The number of bytes that were written
     * @throws FilesystemException
     */
    public function filePutContents($path, $content, $mode = null, $context = null)
    {
        $result = @file_put_contents($this->getScheme() . $path, $content, $mode, $context);
        if (!$result) {
            throw new FilesystemException(
                sprintf('The specified "%s" file could not be written %s', $path, $this->getWarningMessage())
            );
        }
        return $result;
    }

    /**
     * Open file
     *
     * @param string $path
     * @param string $mode
     * @return resource file
     * @throws FilesystemException
     */
    public function fileOpen($path, $mode)
    {
        $urlProp = parse_url($this->getScheme() . $path);

        if (false === $urlProp) {
            throw new FilesystemException(__('Please correct the download URL.'));
        }

        $hostname = $urlProp['host'];
        $port = 80;
        if (isset($urlProp['port'])) {
            $port = (int)$urlProp['port'];
        }

        $path = '/';
        if (isset($urlProp['path'])) {
            $path = $urlProp['path'];
        }

        $query = '';
        if (isset($urlProp['query'])) {
            $query = '?' . $urlProp['query'];
        }

        $result = @fsockopen($hostname, $port, $errorNumber, $errorMessage);

        if ($result === false) {
            throw new FilesystemException(
                __('Something went wrong connecting to the host. Error#%1 - %2.', $errorNumber, $errorMessage)
            );
        }

        $headers = 'GET ' .
            $path .
            $query .
            ' HTTP/1.0' .
            "\r\n" .
            'Host: ' .
            $hostname .
            "\r\n" .
            'User-Agent: Magento ver/' .
            \Magento\Framework\AppInterface::VERSION .
            "\r\n" .
            'Connection: close' .
            "\r\n" .
            "\r\n";

        fwrite($result, $headers);

        // trim headers
        while (!feof($result)) {
            $str = fgets($result, 1024);
            if ($str == "\r\n") {
                break;
            }
        }

        return $result;
    }

    /**
     * Reads the line content from file pointer (with specified number of bytes from the current position).
     *
     * @param resource $resource
     * @param int $length
     * @param string $ending [optional]
     * @return string
     * @throws FilesystemException
     */
    public function fileReadLine($resource, $length, $ending = null)
    {
        $result = @stream_get_line($resource, $length, $ending);

        return $result;
    }

    /**
     * @param string $basePath
     * @param string $path
     * @param string|null $scheme
     * @return string
     */
    public function getAbsolutePath($basePath, $path, $scheme = null)
    {
        return $this->getScheme() . $basePath . $path;
    }

    /**
     * Return path with scheme
     *
     * @param null|string $scheme
     * @return string
     */
    protected function getScheme($scheme = null)
    {
        $scheme = $scheme ?: $this->scheme;
        return $scheme ? $scheme . '://' : '';
    }
}
