<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\File\Pdf\ImageResource;

use Exception;
use finfo;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Zend_Pdf_Exception;

class ImageFactory
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * New zend image factory instance
     *
     * @param string $filename
     * @return \Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff|object
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function factory(string $filename)
    {
        $mediaReader = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        if (!$mediaReader->isFile($filename)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Cannot create image resource. File not found.");
        }
        $tempFilenameFromBucketOrDisk = $this->createTemporaryFileAndPutContent($mediaReader, $filename);
        $tempResourceFilePath = $this->getFilePathOfTemporaryFile($tempFilenameFromBucketOrDisk);
        $typeOfImage = $this->getTypeOfImage($tempResourceFilePath, $filename);
        $zendPdfImage = $this->getZendPdfImage($typeOfImage, $tempResourceFilePath);
        $this->removeTemoraryFile($tempFilenameFromBucketOrDisk);
        return $zendPdfImage;
    }

    /**
     * Create a temporary file and put content of the original file into it
     *
     * @param ReadInterface $mediaReader
     * @param string $filename
     * @return resource
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function createTemporaryFileAndPutContent(ReadInterface $mediaReader, string $filename)
    {
        $tempFilenameFromBucketOrDisk = tmpfile();
        if ($tempFilenameFromBucketOrDisk === false) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Cannot create temporary file');
        }
        fwrite($tempFilenameFromBucketOrDisk, $mediaReader->readFile($filename));
        return $tempFilenameFromBucketOrDisk;
    }

    /**
     * Returns the path of the temporary file or nothing
     *
     * @param resource $tempFilenameFromBucketOrDisk
     * @return string
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function getFilePathOfTemporaryFile($tempFilenameFromBucketOrDisk): string
    {
        try {
            return stream_get_meta_data($tempFilenameFromBucketOrDisk)['uri'];
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Get mime-type in safe way except internal errors
     *
     * @param string $filepath
     * @param string $baseFileName
     * @return mixed|string
     * @throws \Zend_Pdf_Exception
     */
    protected function getTypeOfImage(string $filepath, string $baseFileName)
    {
        if (class_exists('finfo', false) && !empty($filepath)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $classicMimeType = $finfo->file($filepath);
        } elseif (function_exists('mime_content_type') && !empty($filepath)) {
            $classicMimeType = mime_content_type($filepath);
        } else {
            $classicMimeType = $this->fetchFallbackMimeType($baseFileName);
        }
        if (!empty($classicMimeType)) {
            return explode("/", $classicMimeType)[1] ?? '';
        } else {
            return '';
        }
    }

    /**
     * Fall back fetching of mimetype by original base file name
     *
     * @param string $baseFileName
     * @return string
     * @throws \Zend_Pdf_Exception
     */
    protected function fetchFallbackMimeType(string $baseFileName): string
    {
        $extension = pathinfo($baseFileName, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'jpg':
                //Fall through to next case;
            case 'jpe':
                //Fall through to next case;
            case 'jpeg':
                $classicMimeType = 'image/jpeg';
                break;
            case 'png':
                $classicMimeType = 'image/png';
                break;
            case 'tif':
                //Fall through to next case;
            case 'tiff':
                $classicMimeType = 'image/tiff';
                break;
            default:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception(
                    "Cannot create image resource. File extension not known or unsupported type."
                );
        }
        return $classicMimeType;
    }

    /**
     * Creates instance of Zend_Pdf_Resource_Image
     *
     * @param string $typeOfImage
     * @param string $tempResourceFilePath
     * @return \Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff|object
     */
    protected function getZendPdfImage(string $typeOfImage, string $tempResourceFilePath)
    {
        $classToUseAsPdfImage = sprintf('Zend_Pdf_Resource_Image_%s', ucfirst($typeOfImage));
        return new $classToUseAsPdfImage($tempResourceFilePath);
    }

    /**
     * Removes the temporary file from disk
     *
     * @param resource $tempFilenameFromBucketOrDisk
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    protected function removeTemoraryFile($tempFilenameFromBucketOrDisk): void
    {
        fclose($tempFilenameFromBucketOrDisk);
    }
}
