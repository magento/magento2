<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config image field backend model for Zend PDF generator
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Backend\Image;

/**
 * @api
 * @since 2.0.0
 */
class Pdf extends \Magento\Config\Model\Config\Backend\Image
{
    /**
     * @return string[]
     * @since 2.0.0
     */
    protected function _getAllowedExtensions()
    {
        return ['tif', 'tiff', 'png', 'jpg', 'jpe', 'jpeg'];
    }
}
