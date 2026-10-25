<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
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
