<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$rootDir = realpath(__DIR__ . '/../../../..');
require $rootDir . '/app/autoload.php';
\Magento\Framework\Filesystem\FileResolver::addIncludePath([$rootDir . '/lib', $rootDir . '/dev']);
$defaultReportFile = 'report.log';

try {
    $options = new \Zend_Console_Getopt(
        [
            'file=s' => "File containing json encoded acl identifier map (old => new)",
            'mode|w' => "Application mode.  Preview mode is default. If set to 'write' - database is updated.",
            'output|f-w' => "Report output type. Report is flushed to console by default." .
            "If set to 'file', report is written to file /log/report.log",
            'dbprovider=w' => "Database adapter class name. Default: \Magento\Framework\DB\Adapter\Pdo\Mysql",
            'dbhost=s' => "Database server host",
            'dbuser=s' => "Database server user",
            'dbpassword=s' => "Database server password",
            'dbname=s' => "Database name",
            'dbtable=s' => "Table containing resource ids",
        ]
    );

    $fileReader = new \Magento\Tools\Migration\Acl\Db\FileReader();

    $map = $fileReader->extractData($options->getOption('file'));

    $dbAdapterFactory = new \Magento\Tools\Migration\Acl\Db\Adapter\Factory();

    $dbAdapter = $dbAdapterFactory->getAdapter(
        $dbConfig = [
            'host' => $options->getOption('dbhost'),
            'username' => $options->getOption('dbuser'),
            'password' => $options->getOption('dbpassword'),
            'dbname' => $options->getOption('dbname'),
        ],
        $options->getOption('dbprovider')
    );

    $loggerFactory = new \Magento\Tools\Migration\Acl\Db\Logger\Factory();
    $logger = $loggerFactory->getLogger($options->getOption('output'), $defaultReportFile);

    $writer = new \Magento\Tools\Migration\Acl\Db\Writer($dbAdapter, $options->getOption('dbtable'));
    $reader = new \Magento\Tools\Migration\Acl\Db\Reader($dbAdapter, $options->getOption('dbtable'));

    $updater = new \Magento\Tools\Migration\Acl\Db\Updater($reader, $writer, $logger, $options->getOption('mode'));
    $updater->migrate($map);

    $logger->report();
} catch (\Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit;
} catch (\InvalidArgumentException $exp) {
    echo $exp->getMessage();
} catch (\Exception $exp) {
    echo $exp->getMessage();
}
