<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model\Filesystem;

/**
 * Creates a new file based on the file name parameter.
 */
class SplFileInfoFactory
{
    /**
     * Creates SplFileInfo from filename
     *
     * @param string $fileName
     * @return \SplFileInfo
     */
    public function create(string $fileName) : \SplFileInfo
    {
        return new \SplFileInfo($fileName);
    }
}
