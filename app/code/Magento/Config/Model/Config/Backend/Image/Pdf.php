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
 * @deprecated The wrong file type extensions are returned.
 * @see \Magento\Config\Model\Config\Backend\File\Pdf
 */
class Pdf extends \Magento\Config\Model\Config\Backend\Image
{
    /**
     * @inheritdoc
     */
    protected function _getAllowedExtensions()
    {
        return ['tif', 'tiff', 'png', 'jpg', 'jpe', 'jpeg', 'pdf'];
    }
}
