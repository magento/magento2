<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\FileList;

/**
 * View file list collate interface
 */
interface CollateInterface
{
    /**
     * Collate view files
     *
     * @param \Magento\Framework\View\File[] $files
     * @param \Magento\Framework\View\File[] $filesOrigin
     * @return \Magento\Framework\View\File[]
     */
    public function collate($files, $filesOrigin);
}
