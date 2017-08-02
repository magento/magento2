<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File\FileList;

/**
 * View file list collator
 * @since 2.0.0
 */
class Collator implements CollateInterface
{
    /**
     * Collate view files
     *
     * @param \Magento\Framework\View\File[] $files
     * @param \Magento\Framework\View\File[] $filesOrigin
     * @return \Magento\Framework\View\File[]
     * @throws \LogicException
     * @since 2.0.0
     */
    public function collate($files, $filesOrigin)
    {
        foreach ($files as $file) {
            $identifier = $file->getFileIdentifier();
            if (!array_key_exists($identifier, $filesOrigin)) {
                throw new \LogicException(
                    "Overriding view file '{$file->getFilename()}' does not match to any of the files."
                );
            }
            $filesOrigin[$identifier] = $file;
        }
        return $filesOrigin;
    }
}
