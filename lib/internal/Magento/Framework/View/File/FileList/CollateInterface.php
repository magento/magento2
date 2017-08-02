<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\FileList;

/**
 * View file list collate interface
 * @since 2.0.0
 */
interface CollateInterface
{
    /**
     * Collate view files
     *
     * @param \Magento\Framework\View\File[] $files
     * @param \Magento\Framework\View\File[] $filesOrigin
     * @return \Magento\Framework\View\File[]
     * @since 2.0.0
     */
    public function collate($files, $filesOrigin);
}
