<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tools\Webdev\CliParams;
use Magento\Tools\View\Deployer\Log;

require __DIR__ . '/../../../bootstrap.php';

try {
    $opt = new \Zend_Console_Getopt(
        [
            'locale=s'  => 'locale, default: en_US',
            'area=s'    => 'area, one of (frontend|adminhtml|doc), default: frontend',
            'theme=s'   => 'theme in format Vendor/theme, default: Magento/blank',
            'files=s'   => 'files to pre-process (accept more than one file type as comma-separate values),'
                . ' default: css/styles-m',
            'ext=s'     => 'dynamic stylesheet language: less|sass',
            'verbose|v' => 'provide extra output',
            'help|h'    => 'show help',
        ]
    );

    $opt->parse();

    if ($opt->getOption('help')) {
        echo $opt->getUsageMessage();
        exit(0);
    }

    $params = new CliParams($opt);
    $logger = new Log($params->getVerbose());

} catch (\Zend_Console_Getopt_Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    echo 'Please, use quotes(") for wrapping strings.' . PHP_EOL;
    exit(1);
}

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication(
    'Magento\Tools\Webdev\App\FileAssembler',
    ['params' => $params, 'logger' => $logger]
);
$bootstrap->run($app);
