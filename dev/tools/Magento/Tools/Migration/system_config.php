<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
use Magento\Framework\Autoload\IncludePath;
use Magento\Tools\Migration\System\Configuration\Formatter;
use Magento\Tools\Migration\System\Configuration\Generator;
use Magento\Tools\Migration\System\Configuration\Mapper\Field;
use Magento\Tools\Migration\System\Configuration\Mapper\Group;
use Magento\Tools\Migration\System\Configuration\Mapper\Section;
use Magento\Tools\Migration\System\Configuration\Mapper\Tab;
use Magento\Tools\Migration\System\Configuration\Mapper;
use Magento\Tools\Migration\System\Configuration\Parser;
use Magento\Tools\Migration\System\Configuration\Reader;
use Magento\Tools\Migration\System\FileManager;
use Magento\Tools\Migration\System\Writer\Factory;
use Magento\Tools\Migration\System\Configuration\Logger as Logger;

$rootDir = realpath(__DIR__ . '../../../../../../');
require __DIR__ . '/../../../../../app/autoload.php';
(new IncludePath())->addIncludePath(array($rootDir . '/lib', $rootDir . '/dev'));
$defaultReportFile = 'report.log';

try {
    $options = new \Zend_Console_Getopt(
        [
            'mode|w' => "Application mode.  Preview mode is default. If set to 'write' - file system is updated",
            'output|f-w' => "Report output type. Report is flushed to console by default." .
                "If set to 'file', report is written to file /log/report.log"
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
