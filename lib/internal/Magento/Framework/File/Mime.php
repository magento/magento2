<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\File;

/**
 * Utility for mime type retrieval
 */
class Mime
{
    /**
     * Mime types
     *
     * @var array
     */
    protected $mimeTypes = [
        'txt'  => 'text/plain',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'php'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'swf'  => 'application/x-shockwave-flash',
        'flv'  => 'video/x-flv',

        // images
        'png'  => 'image/png',
        'jpe'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',
        'svg'  => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed',
        'exe'  => 'application/x-msdownload',
        'msi'  => 'application/x-msdownload',
        'cab'  => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3'  => 'audio/mpeg',
        'qt'   => 'video/quicktime',
        'mov'  => 'video/quicktime',

        // adobe
        'pdf'  => 'application/pdf',
        'psd'  => 'image/vnd.adobe.photoshop',
        'ai'   => 'application/postscript',
        'eps'  => 'application/postscript',
        'ps'   => 'application/postscript',
    ];

    /**
     * List of mime types that can be defined by file extension.
     *
     * @var array
     */
    private $defineByExtensionList = [
        'txt'  => 'text/plain',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'php'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
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
    ];

    /**
     * Get mime type of a file
     *
     * @param string $file
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getMimeType($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("File '$file' doesn't exist");
        }

        $result = null;
        $extension = $this->getFileExtension($file);

        if (function_exists('mime_content_type')) {
            $result = $this->getNativeMimeType($file);
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
     * @param string $file
     * @return string
     */
    private function getFileExtension(string $file): string
    {
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }
}
