<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Backend;

/**
 * Class \Magento\Theme\Model\Design\Backend\Image
 *
 * @since 2.1.0
 */
class Image extends File
{
    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     * @since 2.1.0
     */
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
