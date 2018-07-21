<?php
/**
 * @deprecated The global function __() is now loaded via Composer in the Magento Framework, the below require is only
 *             for backwards compatibility reasons and this file will be removed in a future version
 * @see        Magento\Framework\Phrase\__.php
 */

$vendorDir = require VENDOR_PATH;
if (!function_exists('__')) {
    if (file_exists(BP . '/lib/internal/Magento/Framework/Phrase/__.php')) {
        require BP . '/lib/internal/Magento/Framework/Phrase/__.php';
    } elseif (file_exists(BP . "/{$vendorDir}/magento/framework/Phrase/__.php")) {
        require BP . "/{$vendorDir}/magento/framework/Phrase/__.php";
    }
}
