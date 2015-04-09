<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

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
try {
    if (PHP_SAPI == 'cli') {
        throw new \Exception("You cannot run it as a script from commandline." . PHP_EOL);
    }
    \Zend\Mvc\Application::init(require __DIR__ . '/config/application.config.php')->run();
} catch (\Exception $e) {
    echo $e->getMessage();
}

