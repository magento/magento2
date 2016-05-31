<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\File\FileList;

use Magento\Framework\View\File\FileList\CollateInterface;

/**
 * File list collator
 */
class Collator implements CollateInterface
{
    /**
     * Collate source files
     *
     * @param \Magento\Framework\View\File[] $files
     * @param \Magento\Framework\View\File[] $filesOrigin
     * @return \Magento\Framework\View\File[]
     */
    public function collate($files, $filesOrigin)
    {
        foreach ($files as $file) {
            $fileId = substr($file->getFileIdentifier(), strpos($file->getFileIdentifier(), '|'));
            foreach (array_keys($filesOrigin) as $identifier) {
                if (false !== strpos($identifier, $fileId)) {
                    unset($filesOrigin[$identifier]);
                }
            }
            $filesOrigin[$file->getFileIdentifier()] = $file;
        }
        return $filesOrigin;
    }
}
