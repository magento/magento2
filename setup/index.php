<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Setup\Application;
use Magento\Setup\Model\ObjectManagerProvider;

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

$configuration = require __DIR__ . '/config/application.config.php';
$bootstrap = new Application();
$application = $bootstrap->bootstrap($configuration);
$application->getServiceManager()
    ->get(ObjectManagerProvider::class)
    ->setObjectManager(\Magento\Framework\App\Bootstrap::create(BP, $_SERVER)->getObjectManager());
$application->run();
