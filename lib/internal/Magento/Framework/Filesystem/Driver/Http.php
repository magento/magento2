<?php
/**
 * Origin filesystem driver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Driver;

use Magento\Framework\Exception\FileSystemException;

/**
 * Class Http
 *
 * @since 2.0.0
 */
class Http extends File
{
    /**
     * Scheme distinguisher
     *
     * @var string
     * @since 2.0.0
     */
    protected $scheme = 'http';

    /**
     * Checks if path exists
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function stat($path)
    {
        $headers = array_change_key_case(get_headers($this->getScheme() . $path, 1), CASE_LOWER);

        $result = [
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
            'disposition' => isset($headers['content-disposition']) ? $headers['content-disposition'] : null,
        ];
        return $result;
    }

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flags
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileGetContents($path, $flags = null, $context = null)
    {
        clearstatcache();
        $result = @file_get_contents($this->getScheme() . $path, $flags, $context);
        if (false === $result) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase(
                    'Cannot read contents from file "%1" %2',
                    [$path, $this->getWarningMessage()]
                )
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
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function filePutContents($path, $content, $mode = null, $context = null)
    {
        $result = @file_put_contents($this->getScheme() . $path, $content, $mode, $context);
        if (!$result) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase(
                    'The specified "%1" file could not be written %2',
                    [$path, $this->getWarningMessage()]
                )
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
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function fileOpen($path, $mode)
    {
        $urlProp = $this->parseUrl($this->getScheme() . $path);

        if (false === $urlProp) {
            throw new FileSystemException(new \Magento\Framework\Phrase('Please correct the download URL.'));
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

        $result = $this->open($hostname, $port);

        $headers = 'GET ' .
            $path .
            $query .
            ' HTTP/1.0' .
            "\r\n" .
            'Host: ' .
            $hostname .
            "\r\n" .
            'User-Agent: Magento' .
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
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function fileReadLine($resource, $length, $ending = null)
    {
        $result = @stream_get_line($resource, $length, $ending);

        return $result;
    }

    /**
     * Get absolute path
     *
     * @param string $basePath
     * @param string $path
     * @param string|null $scheme
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getAbsolutePath($basePath, $path, $scheme = null)
    {
        // check if the path given is already an absolute path containing the
        // basepath. so if the basepath starts at position 0 in the path, we
        // must not concatinate them again because path is already absolute.
        if (0 === strpos($path, $basePath)) {
            return $this->getScheme() . $path;
        }

        return $this->getScheme() . $basePath . $path;
    }

    /**
     * Return path with scheme
     *
     * @param null|string $scheme
     * @return string
     * @since 2.0.0
     */
    protected function getScheme($scheme = null)
    {
        $scheme = $scheme ?: $this->scheme;
        return $scheme ? $scheme . '://' : '';
    }

    /**
     * Open a url
     *
     * @param string $hostname
     * @param int $port
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return array
     * @since 2.0.0
     */
    protected function open($hostname, $port)
    {
        $result = @fsockopen($hostname, $port, $errorNumber, $errorMessage);
        if ($result === false) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase(
                    'Something went wrong while connecting to the host. Error#%1 - %2.',
                    [$errorNumber, $errorMessage]
                )
            );
        }
        return $result;
    }

    /**
     * Parse a http url
     *
     * @param string $path
     * @return array
     * @since 2.0.0
     */
    protected function parseUrl($path)
    {
        return parse_url($path);
    }
}
