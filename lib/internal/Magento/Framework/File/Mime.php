<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

/**
 * Utility for mime type retrieval
 *
 * @deprecated
 * @see Filesystem\ExtendedDriverInterface::getMetadata()
 */
class Mime
{
    /**
     * Mime types
     *
     * @var array
     *
     * @deprecated
     */
    protected $mimeTypes = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
    ];

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem|null $filesystem
     */
    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
    }

    /**
     * Get mime type of a file.
     *
     * @param string $file
     * @return string
     * @throws FileSystemException
     *
     * @deprecated
     */
    public function getMimeType($file)
    {
        $driver = $this->filesystem->getDirectoryWrite(
            DirectoryList::ROOT,
            Filesystem\DriverPool::FILE
        )->getDriver();

        /**
         * Try with non-local driver.
         */
        if (!$driver->isExists($file)) {
            $driver = $this->filesystem->getDirectoryWrite(
                DirectoryList::ROOT
            )->getDriver();
        }

        if (!$driver->isExists($file)) {
            throw new FileSystemException(__("File '$file' doesn't exist"));
        }

        if ($driver instanceof Filesystem\ExtendedDriverInterface) {
            return $driver->getMetadata($file)['mimetype'];
        }

        $mime = new Filesystem\Driver\File\Mime();

        return $mime->getMimeType($file);
    }
}
