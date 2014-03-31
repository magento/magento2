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
require __DIR__ . '/../../../bootstrap.php';
$rootDir = realpath(__DIR__ . '/../../../../../');
use Magento\Tools\Layout\Reference\Processor;
use Magento\Tools\Layout\Formatter;

try {
    $opt = new \Zend_Console_Getopt(
        array(
            'dir=s' => "Directory to process(optional, default {$rootDir})",
            'file|f=s' => 'File to process(optional)',
            'overwrite|o' => 'Overwrite file',
            'collect|c' => 'Collect names for a dictionary',
            'process|p' => 'Process references using dictionary',
            'dictionary|d=s' => 'Dictionary file (required)',
            'processor=s' => 'Processor file (optional)'
        )
    );
    $opt->parse();

    if ($opt->dir) {
        $rootDir = realpath($opt->dir);
    }
    if (!file_exists($rootDir) || !is_dir($rootDir)) {
        throw new \Exception("Directory to process ({$rootDir}) not found");
    }
    if (empty($opt->dictionary)) {
        throw new \Exception("Dictionary file is required");
    }

    if (!empty($opt->process) && empty($opt->file) && empty($opt->overwrite)) {
        throw new \Exception("Overwrite option is required if you going to process multiple files");
    }

    if (!file_exists($opt->dictionary)) {
        touch($opt->dictionary);
    }

    $processor = new Processor(new Formatter(), realpath($opt->dictionary));

    $layouts = array();
    if (!empty($opt->file) && file_exists($opt->file)) {
        $layouts = array(realpath($opt->file));
    } else {
        $layouts = $processor->getLayoutFiles($rootDir);
    }

    if ($opt->collect) {
        $processor->getReferences($layouts)->writeToFile();
    }

    if ($opt->process) {
        $processor->updateReferences($layouts, $opt->processor, $opt->overwrite);
    }

    exit(0);
} catch (\Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit(255);
} catch (\Exception $e) {
    echo $e, PHP_EOL;
    exit(255);
}
