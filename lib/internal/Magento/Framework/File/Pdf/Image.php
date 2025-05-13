<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Pdf;

use Magento\Framework\File\Pdf\ImageResource\ImageFactory;

class Image
{
    /**
     * @var \Magento\Framework\File\Pdf\ImageResource\ImageFactory
     */
    private ImageFactory $imageFactory;

    /**
     * @param \Magento\Framework\File\Pdf\ImageResource\ImageFactory $imageFactory
     */
    public function __construct(ImageFactory $imageFactory)
    {
        $this->imageFactory = $imageFactory;
    }

    /**
     * Filepath of image file
     *
     * @param string $filePath
     * @return \Zend_Pdf_Resource_Image|\Zend_Pdf_Resource_Image_Jpeg|\Zend_Pdf_Resource_Image_Png|\Zend_Pdf_Resource_Image_Tiff|object
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Zend_Pdf_Exception
     */
    public function imageWithPathAdvanced(string $filePath)
    {
        return $this->imageFactory->factory($filePath);
    }
}
