<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;

return [
    InitParamListener::BOOTSTRAP_PARAM => [
        Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [DirectoryList::ROOT => [DirectoryList::PATH => BP]],
    ]
];
