<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config image field backend model
 */
namespace Magento\Config\Model\Config\Backend;

class Image extends File
{
    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
