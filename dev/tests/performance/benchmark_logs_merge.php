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
 * @category    Magento
 * @package     performance_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$magentoBaseDir = realpath(__DIR__ . '/../../../');
require_once $magentoBaseDir. '/lib/Zend/Console/Getopt.php';

$shell = new Zend_Console_Getopt(array(
    'xml=s'  => 'xml',
    'csv=s'  => 'csv',
    'logs=s' => 'logs'
));

$args = $shell->getOptions();
if (empty($args)) {
    echo $shell->getUsageMessage();
    exit(1);
}

$xmlUrl = $shell->getOption('xml');
$scvUrl = $shell->getOption('csv');
$newLogsUrl = $shell->getOption('logs');

if (!file_exists($xmlUrl)) {
    echo 'xml does not exist';
    exit(1);
}

if (!file_exists($scvUrl)) {
    echo 'csv does not exist';
    exit(1);
}

$xml = simplexml_load_file($xmlUrl);
$scv = readCsv($scvUrl);

foreach ($xml as $key => $value) {
    unset($value->httpSample);
    unset($value->assertionResult);
}

foreach ($scv as $key => $value) {
    $httpSample = $xml->addChild('httpSample');

    $httpSample->addAttribute('t', $value[1]);
    $httpSample->addAttribute('lt', $value[1]);
    $httpSample->addAttribute('ts', $value[0]);
    $httpSample->addAttribute('s', 'true');
    $httpSample->addAttribute('lb', $value[2]);
    $httpSample->addAttribute('rc', '200');
    $httpSample->addAttribute('rm', 'OK');
    $httpSample->addAttribute('tn', $value[2]);
}

$xml->asXML($newLogsUrl);

function readCsv($csvFile)
{
    $fileHandle = fopen($csvFile, 'r');
    $lineOfText = array();
    while (!feof($fileHandle) ) {
        $lineOfText[] = fgetcsv($fileHandle, 1024);
    }
    fclose($fileHandle);
    return $lineOfText;
}
