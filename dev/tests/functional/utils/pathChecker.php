<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// phpcs:ignore Magento2.Security.Superglobal
if (isset($_GET['path'])) {
    // phpcs:ignore Magento2.Security.Superglobal
    $path = urldecode($_GET['path']);
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    if (file_exists('../../../../' . $path)) {
        // phpcs:ignore Magento2.Security.LanguageConstruct
        echo 'path exists: true';
    } else {
        // phpcs:ignore Magento2.Security.LanguageConstruct
        echo 'path exists: false';
    }
} else {
    // phpcs:ignore Magento2.Exceptions.DirectThrow
    throw new \InvalidArgumentException("GET parameter 'path' is not set.");
}
