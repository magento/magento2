<?php

namespace Magento\Framework\File\Pdf;

use Magento\Framework\File\Pdf\ImageResource\ImageFactory;
use Zend_Pdf_Image;

abstract class Image extends Zend_Pdf_Image
{
    /**
     * Filepath of image file
     *
     * @param string $filePath
     * @return \Zend_Pdf_Resource_Image|\Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff|object
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    public static function imageWithPath($filePath)
    {
        return ImageFactory::factory($filePath);
    }
}
