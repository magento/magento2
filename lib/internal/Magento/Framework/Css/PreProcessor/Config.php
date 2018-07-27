<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor;

use Magento\Framework\App\Filesystem\DirectoryList;

class Config
{
    /**
     * Temporary directory prefix
     */
    const TMP_DIR = 'css';

    /**
     * Returns relative path to materialization directory
     *
     * @return string
     */
    public function getMaterializationRelativePath()
    {
        return DirectoryList::TMP_MATERIALIZATION_DIR . '/' . self::TMP_DIR;
    }
}
