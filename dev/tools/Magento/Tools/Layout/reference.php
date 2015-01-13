<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../../bootstrap.php';
$rootDir = realpath(__DIR__ . '/../../../../../');
use Magento\Tools\Layout\Formatter;
use Magento\Tools\Layout\Reference\Processor;

try {
    $opt = new \Zend_Console_Getopt(
        [
            'dir=s' => "Directory to process(optional, default {$rootDir})",
            'file|f=s' => 'File to process(optional)',
            'overwrite|o' => 'Overwrite file',
            'collect|c' => 'Collect names for a dictionary',
            'process|p' => 'Process references using dictionary',
            'dictionary|d=s' => 'Dictionary file (required)',
            'processor=s' => 'Processor file (optional)',
        ]
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

    $layouts = [];
    if (!empty($opt->file) && file_exists($opt->file)) {
        $layouts = [realpath($opt->file)];
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
