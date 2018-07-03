<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Glob patterns relative to the project root directory, used by
 * registration.php to generate a list of includes.
 */
return [
    'app/code/*/*/cli_commands.php',
    'app/code/*/*/registration.php',
    'app/design/*/*/*/registration.php',
    'app/i18n/*/*/registration.php',
    'lib/internal/*/*/registration.php',
    'lib/internal/*/*/*/registration.php',
    'setup/src/*/*/registration.php'
];
