<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$usageMessage =
'Usage:' . PHP_EOL
. '   php -f ' . str_replace(dirname(__FILE__), __FILE__, '')
. ' -- -m mainline_report.jtl -b branch_report.jtl -o output_file.xml ";"' . PHP_EOL
. PHP_EOL
. 'Parameters:' . PHP_EOL
. '   -m   - mainline report file' . PHP_EOL
. '   -b   - branch report file' . PHP_EOL
. '   -o   - output xml file' . PHP_EOL
. '   -p   - percent of measurements, that will be skipped (default = 15)' . PHP_EOL;

$args = getopt('m:b:o:p::');
if (empty($args)) {
    echo $usageMessage;
    exit(0);
}

$mainlineFile = $args['m'];
$branchFile = $args['b'];
$outputFile = $args['o'];
$skipMeasurementsPercent = isset($args['p']) && $args['p'] != '' ? min(100, max(0, $args['p'])) : 15;

try {
    $mainlineResults = readResponseTimeReport($mainlineFile);
    $branchResults = readResponseTimeReport($branchFile);

    $result = new SimpleXMLElement('<testResults version="1.2" />');
    foreach (array_keys($mainlineResults) as $sampleName) {
        $success = isset($mainlineResults[$sampleName]['success'])
            && $mainlineResults[$sampleName]['success']
            && isset($branchResults[$sampleName])
            && isset($branchResults[$sampleName]['success'])
            && $branchResults[$sampleName]['success'];

        $deviation = $success
            ? getDeviation($mainlineResults[$sampleName]['times'], $branchResults[$sampleName]['times'])
            : 100;

        $sample = $result->addChild('httpSample');
        $sample->addAttribute('s', $success ? 'true' : 'false');
        $sample->addAttribute('t', round($deviation * 1000));
        $sample->addAttribute('lb', $sampleName . ' degradation');
    }

    $dom = new DOMDocument("1.0");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($result->asXML());
    file_put_contents($outputFile, $dom->saveXML());
} catch (\Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

function readResponseTimeReport($filename)
{
    $result = [];
    $f = fopen($filename, 'r');
    while (!feof($f) && is_array($line = fgetcsv($f))) {
        $responseTime = $line[1];
        $title = $line[2];
        $success = $line[7];
        if (!isset($result[$title])) {
            $result[$title] = ['times' => [], 'success' => true];
        }

        $result[$title]['times'][] = $responseTime;
        $result[$title]['success'] &= ($success == 'true');
    }
    return $result;
}

function getMeanValue(array $times)
{
    global $skipMeasurementsPercent;
    sort($times);
    $slice = array_slice($times, 0, round(count($times) - count($times) * $skipMeasurementsPercent / 100));

    return array_sum($slice) / count($slice);
}

function getDeviation(array $mainlineResults, array $branchResults)
{
    return 100 * (getMeanValue($branchResults) / getMeanValue($mainlineResults) - 1);
}
