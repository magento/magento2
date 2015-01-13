<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$basePath = realpath(__DIR__ . '/../../../');
require_once $basePath . '/app/autoload.php';
require __DIR__ . '/Formatter.php';

try {
    $opt = new Zend_Console_Getopt([
        'file|f=s' => 'File to process(required)',
        'processor|p=s' => 'Processor file (required)',
        'overwrite|o' => 'Overwrite file',
    ]);
    $opt->parse();

    $doc  = new DOMDocument();
    $doc->preserveWhiteSpace = true;
    $doc->load($opt->file);

    $stylesheet = new DOMDocument();
    $stylesheet->preserveWhiteSpace = true;
    $stylesheet->load($opt->processor);

    $formater = new \Magento\Tools\Layout\Formatter();

    $xslt = new XSLTProcessor();
    $xslt->registerPHPFunctions();
    $xslt->importStylesheet($stylesheet);
    $transformedDoc = $xslt->transformToXml($doc);
    $result = $formater->format($transformedDoc);
    if ($opt->overwrite) {
        file_put_contents($opt->file, $result);
    } else {
        echo $result;
    }
    exit(0);
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit(255);
} catch (Exception $e) {
    echo $e, PHP_EOL;
    exit(255);
}
