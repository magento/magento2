<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
    $globPatterns = include  __DIR__ . '/include-globlist.php';
    $baseDir = dirname(__DIR__) . '/';

    foreach ($globPatterns as $globPattern) {
        // Sorting is disabled intentionally for performance improvement
        $files = glob($baseDir . $globPattern, GLOB_NOSORT);
        if ($files === false) {
            throw new RuntimeException("glob(): error with '$baseDir$globPattern'");
        }
        array_map(__NAMESPACE__ . '\file', $files);
    }
}

/**
 * Isolated include with it's own variable scope
 *
 * @return void
 */
function file() {
    include func_get_arg(0);
}

main();
