<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config\Backend\Image;

/**
 * @api
 */
class Pdf extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['pdf'];
    }
}
