<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Tools\Migration\System\Configuration\Formatter;
use Magento\Tools\Migration\System\Configuration\Generator;
use Magento\Tools\Migration\System\Configuration\Logger as Logger;
use Magento\Tools\Migration\System\Configuration\Mapper;
use Magento\Tools\Migration\System\Configuration\Mapper\Field;
use Magento\Tools\Migration\System\Configuration\Mapper\Group;
use Magento\Tools\Migration\System\Configuration\Mapper\Section;
use Magento\Tools\Migration\System\Configuration\Mapper\Tab;
use Magento\Tools\Migration\System\Configuration\Parser;
use Magento\Tools\Migration\System\Configuration\Reader;
use Magento\Tools\Migration\System\FileManager;
use Magento\Tools\Migration\System\Writer\Factory;

$rootDir = realpath(__DIR__ . '../../../../../../');

require __DIR__ . '/../../../../../app/autoload.php';
\Magento\Framework\Filesystem\FileResolver::addIncludePath([$rootDir . '/lib', $rootDir . '/dev']);

$defaultReportFile = 'report.log';

try {
    $options = new \Zend_Console_Getopt(
        [
            'mode|w' => "Application mode.  Preview mode is default. If set to 'write' - file system is updated",
            'output|f-w' => "Report output type. Report is flushed to console by default." .
                "If set to 'file', report is written to file /log/report.log",
        ]
    );

    $writerFactory = new Factory();

    $fileManager = new FileManager(
        new \Magento\Tools\Migration\System\FileReader(),
        $writerFactory->getWriter($options->getOption('mode'))
    );

    $loggerFactory = new Logger\Factory();
    $logger = $loggerFactory->getLogger($options->getOption('output'), $defaultReportFile, $fileManager);

    $generator = new Generator(
        new Formatter(),
        $fileManager,
        $logger
    );

    $fieldMapper = new Field();
    $groupMapper = new Group($fieldMapper);
    $sectionMapper = new Section($groupMapper);
    $tabMapper = new Tab();
    $mapper = new Mapper($tabMapper, $sectionMapper);

    $parser = new Parser();
    $reader = new Reader($fileManager, $parser, $mapper);

    foreach ($reader->getConfiguration() as $file => $config) {
        $generator->createConfiguration($file, $config);
        $fileManager->remove($file);
    }
    $logger->report();
} catch (\Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit;
} catch (InvalidArgumentException $exp) {
    echo $exp->getMessage();
} catch (Exception $exp) {
    echo $exp->getMessage();
}
