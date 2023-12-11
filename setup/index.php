<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Laminas\Http\PhpEnvironment\Request;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ProductMetadata;
use Magento\Setup\Model\License;

if (PHP_SAPI == 'cli') {
    echo "You cannot run this from the command line." . PHP_EOL .
        "Run \"php bin/magento\" instead." . PHP_EOL;
    exit(1);
}
try {
    require __DIR__ . '/../app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

// For Setup Wizard we are using our customized error handler
$handler = new \Magento\Framework\App\ErrorHandler();
set_error_handler([$handler, 'handler']);

// Render Setup Wizard landing page
$objectManager = Bootstrap::create(BP, $_SERVER)->getObjectManager();

$licenseClass = $objectManager->create(License::class);
$metaClass = $objectManager->create(ProductMetadata::class);
/** @var License $license */
$license = $licenseClass->getContents();
/** @var ProductMetadata $version */
$version = $metaClass->getVersion();

$request = new Request();
$basePath = $request->getBasePath();

ob_start();
require_once __DIR__ . '/view/magento/setup/index.phtml';
$html = ob_get_clean();
echo $html;
