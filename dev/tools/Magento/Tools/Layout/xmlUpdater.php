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
$basePath = realpath(__DIR__ . '/../../../../../');
require_once $basePath . '/app/autoload.php';
require __DIR__ . '/Formatter.php';

(new \Magento\Framework\Autoload\IncludePath())->addIncludePath(array($basePath . '/lib/internal'));

try {
    $opt = new \Zend_Console_Getopt(
        array(
            'file|f=s' => 'File to process(required)',
            'processor|p=s' => 'Processor file (required)',
            'overwrite|o' => 'Overwrite file'
        )
    );
    $opt->parse();

    $doc = new \DOMDocument();
    $doc->preserveWhiteSpace = true;
    $doc->load($opt->file);

    $stylesheet = new \DOMDocument();
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
} catch (\Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit(255);
} catch (\Exception $e) {
    echo $e, PHP_EOL;
    exit(255);
}
