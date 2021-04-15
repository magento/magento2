<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\File\FileList;

/**
 * View file list collate interface
 *
 * @api
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
