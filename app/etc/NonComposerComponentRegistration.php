<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

//Register components (via a list of glob patterns)
namespace Magento\NonComposerComponentRegistration;

use RuntimeException;

/**
 * Include files from a list of glob patterns
 */
(static function (): void {
    $globPatterns = require __DIR__ . '/registration_globlist.php';
    $baseDir = \dirname(__DIR__, 2) . '/';

    foreach ($globPatterns as $globPattern) {
        // Sorting is disabled intentionally for performance improvement
        $files = \glob($baseDir . $globPattern, GLOB_NOSORT);
        if ($files === false) {
            throw new RuntimeException("glob(): error with '$baseDir$globPattern'");
        }

        \array_map(
            static function (string $file): void {
                require_once $file;
            },
            $files
        );
    }
})();
