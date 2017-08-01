<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config image field backend model
 */
namespace Magento\Config\Model\Config\Backend;

/**
 * @api
 * @since 2.0.0
 */
class Image extends File
{
    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     * @since 2.0.0
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
