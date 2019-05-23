<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
// phpcs:ignore Magento2.Security.Superglobal
if (!isset($_GET['name'])) {
    // phpcs:ignore Magento2.Exceptions.DirectThrow
    throw new \InvalidArgumentException(
        'The name of log file is required for getting logs.'
    );
}

// phpcs:ignore Magento2.Security.Superglobal
$name = urldecode($_GET['name']);
if (preg_match('/\.\.(\\\|\/)/', $name)) {
    throw new \InvalidArgumentException('Invalid log file name');
}

// phpcs:ignore Magento2.Security.InsecureFunction, Magento2.Functions.DiscouragedFunction, Magento2.Security.LanguageConstruct
echo serialize(file_get_contents('../../../../var/log' .'/' .$name));
