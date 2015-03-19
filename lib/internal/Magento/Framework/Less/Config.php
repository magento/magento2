<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Less;

use Magento\Framework\App\Filesystem\DirectoryList;

class Config
{
    /**
     * Temporary directory prefix
     */
    const TMP_LESS_DIR = 'less';

    /**
     * Returns relative path to less materialization directory
     *
     * @return string
     */
    public function getLessMaterializationRelativePath()
    {
        return DirectoryList::TMP_MATERIALIZATION_DIR . '/' . self::TMP_LESS_DIR;
    }
}
