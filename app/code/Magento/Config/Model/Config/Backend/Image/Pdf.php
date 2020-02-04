<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config\Backend\Image;

/**
 * System config PDF field backend model.
 *
 * @api
 * @since 100.0.2
 * @see \Magento\Config\Model\Config\Backend\File\Pdf
 */
class Pdf extends \Magento\Config\Model\Config\Backend\Image
{
    /**
     * Returns the list of allowed file extensions.
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['tif', 'tiff', 'png', 'jpg', 'jpe', 'jpeg', 'pdf'];
    }
}
