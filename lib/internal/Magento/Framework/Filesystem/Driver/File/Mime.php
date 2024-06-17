<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Driver\File;

use Magento\Framework\Exception\FileSystemException;

/**
 * Mime type resolver.
 */
class Mime
{
    /**
     * @var array
     */
    private $mimeTypes = [
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
     * List of mime types that can be defined by file extension.
     *
     * @var array
     */
    private $defineByExtensionList = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'svg' => 'image/svg+xml',
    ];

    /**
     * List of generic MIME types
     *
     * The file mime type should be detected by the file's extension if the native mime type is one of the listed below.
     *
     * @var array
     */
    private $genericMimeTypes = [
        'application/x-empty',
        'inode/x-empty',
        'application/octet-stream'
    ];

    /**
     * Get mime type of a file
     *
     * @param string $path Absolute file path
     * @return string
     * @throws FileSystemException
     */
    public function getMimeType(string $path): string
    {
        if (!file_exists($path)) {
            throw new FileSystemException(__("File '$path' doesn't exist"));
        }

        $result = null;
        $extension = $this->getFileExtension($path);

        if (function_exists('mime_content_type')) {
            $result = $this->getNativeMimeType($path);
        } else {
            $imageInfo = getimagesize($path);
            $result = $imageInfo['mime'];
        }

        if (null === $result && isset($this->mimeTypes[$extension])) {
            $result = $this->mimeTypes[$extension];
        } elseif (null === $result) {
            $result = 'application/octet-stream';
        }

        return $result;
    }

    /**
     * Get mime type by the native mime_content_type function.
     *
     * Search for extended mime type if mime_content_type() returned 'application/octet-stream' or 'text/plain'
     *
     * @param string $file
     * @return string
     */
    private function getNativeMimeType(string $file): string
    {
        $extension = $this->getFileExtension($file);
        $result = mime_content_type($file);
        if (isset($this->mimeTypes[$extension], $this->defineByExtensionList[$extension])
            && (
                strpos($result, 'text/') === 0
                || strpos($result, 'image/svg') === 0
                || in_array($result, $this->genericMimeTypes, true)
            )
        ) {
            $result = $this->mimeTypes[$extension];
        }

        return $result;
    }

    /**
     * Get file extension by file name.
     *
     * @param string $path
     * @return string
     */
    private function getFileExtension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }
}
