<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config\Backend\File;

/**
 * System config PDF field backend model.
 */
class Pdf extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @inheritdoc
     */
    protected function _getAllowedExtensions()
    {
        return ['pdf'];
    }
}
