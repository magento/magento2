<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;

return [
    InitParamListener::BOOTSTRAP_PARAM => [
        Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [DirectoryList::ROOT => [DirectoryList::PATH => BP]],
    ]
];
