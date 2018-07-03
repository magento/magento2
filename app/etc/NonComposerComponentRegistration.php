<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

//Register components (via a list of glob patterns)
namespace Magento\NonComposerComponentRegistration;

use RuntimeException;

/**
 * Include files from a list of glob patterns
 *
 * @throws RuntimeException
 * @return void
 */
function main()
{
    $globPatterns = require __DIR__ . '/registration_globlist.php';
    $baseDir = dirname(dirname(__DIR__)) . '/';

    foreach ($globPatterns as $globPattern) {
        // Sorting is disabled intentionally for performance improvement
        $files = glob($baseDir . $globPattern, GLOB_NOSORT);
        if ($files === false) {
            throw new RuntimeException("glob(): error with '$baseDir$globPattern'");
        }
        array_map(function ($file) { require_once $file; }, $files);
    }
}

main();
