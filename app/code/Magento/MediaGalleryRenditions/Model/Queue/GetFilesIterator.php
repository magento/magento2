<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Model\Queue;

/**
 * Retrieve files iterator for path
 */
class GetFilesIterator
{
    /**
     * Get files iterator for provided path
     *
     * @param string $path
     * @return \RecursiveIteratorIterator
     */
    public function execute(string $path): \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS |
                \FilesystemIterator::UNIX_PATHS |
                \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    }
}
