<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\Framework\Css\PreProcessor\Config
 *
 * @since 2.0.0
 */
class Config
{
    /**
     * Temporary directory prefix
     */
    const TMP_DIR = 'pub/static';

    /**
     * Returns relative path to materialization directory
     *
     * @return string
     * @since 2.0.0
     */
    public function getMaterializationRelativePath()
    {
        return DirectoryList::TMP_MATERIALIZATION_DIR . '/' . self::TMP_DIR;
    }
}
