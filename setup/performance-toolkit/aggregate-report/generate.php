<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
* @SuppressWarnings(PHPMD.CyclomaticComplexity)
* @SuppressWarnings(PHPMD.NPathComplexity)
*/

$usageMessage =
    'Usage:' . PHP_EOL
    . '   php generate.php -j jmeter_report.jtl -m memory_usage.log -o output_file.csv' . PHP_EOL
    . PHP_EOL
    . 'Parameters:' . PHP_EOL
    . '   -j   - jmeter report file' . PHP_EOL
    . '   -m   - memory usage report file (optional)' . PHP_EOL
    . '   -o   - output report file' . PHP_EOL
    . '   -f   - include failed requests in report (optional)' . PHP_EOL;

$args = getopt('j:m:o:f');
if (empty($args['j']) || empty($args['o'])) {
    echo $usageMessage;
    exit(0);
}

require_once("b2c_mappings.php");

list($jmeterData, $executionTime) = parseJmeterReport($args['j'], isset($args['f']));
$memoryUsageData = !empty($args['m']) ? parseMemoryUsageLog($args['m']) : [];
$aggregatedResult = prepareAggregatedResult($jmeterData, $memoryUsageData, $mapping);
parseReportAndWriteToCsv($aggregatedResult, $executionTime, $args['o']);

/**
 * Read memory usage log into array.
 *
 * @param string $memoryUsageReport Path to the memory usage log
 * @return array
 */
function parseMemoryUsageLog($memoryUsageReport)
{
    $file = fopen($memoryUsageReport, 'r');
    $memoryUsage = [];

    while (!feof($file)) {
        $line = preg_split('/\s+/', fgets($file), -1, PREG_SPLIT_NO_EMPTY);
        if (count($line) != 3 || $line[0] < 1024 * 1024) {
            continue;
        }
        $memoryUsage[] = [
            'memory' => $line[0],
            'uri' => $line[2],
        ];
    }
    fclose($file);

    return $memoryUsage;
}

/**
 * Parse JTL report and group all http requests by title.
 *
 * @param string $jmeterReport Path to the JTL report
 * @param bool $includeErrors If true failed requests are included in report
 * @return array First element - requests grouped by title, second - total execution time of the scenario
 */
function parseJmeterReport($jmeterReport, $includeErrors)
{
    $result = [];
    $f = fopen($jmeterReport, 'r');
    $line = fgetcsv($f);
    if (is_array($line) && count($line) > 1) {
        $delimiter_char = ",";
    } elseif (is_array($line = fgetcsv($f, 1000, $delimiter = "|")) && count($line) > 1) {
        $delimiter_char = "|";
    }
    do {
        $responseTime = (int)$line[1];
        if (is_numeric($responseTime)) {
            $title = $line[2];
            if (!$includeErrors) {
                if ($line[7] == 'false') {
                    continue;
                }
            }
            if (!isset($result[$title])) {
                $result[$title] = ['times' => []];
            }
            $result[$title]['times'][] = $responseTime;

            if (strpos($title, 'WarmUp Add To Cart') !== false) {
                $startTime = $line[0];
            }
            if (strpos($title, 'Clear properties') !== false) {
                $endTime = $line[0];
            }
        }
    } while (!feof($f) && is_array($line = fgetcsv($f, 1000, $delimiter = $delimiter_char)));

    return [$result, $endTime - $startTime];
}

/**
 * Aggregate data from JTL and memory log using mapping.
 *
 * @param array $jmeterData
 * @param array $memoryUsageData
 * @param array $mappings
 * @return array
 */
function prepareAggregatedResult(array $jmeterData, array $memoryUsageData, array $mappings)
{
    $aggregatedResult = [];
    foreach ($mappings as $key => $mapping) {
        $aggregatedResult[$key]['label'] = $mapping['label'];
        $aggregatedResult[$key]['scenario'] = $mapping['scenario'] ?? 'Total';
        $aggregatedResult[$key]['is_service_url'] = $mapping['is_service_url'] ?? false;
        $aggregatedResult[$key]['is_storefront'] = $mapping['is_storefront'] ?? false;
        $aggregatedResult[$key]['time'] = [];
        $aggregatedResult[$key]['labels'] = [];
        $aggregatedResult[$key]['order'] = 0;
        $order = 0;
        foreach ($jmeterData as $label => $time) {
            $order++;
            if (strpos($label, 'SetUp') !== false && !$aggregatedResult[$key]['is_service_url']) {
                continue;
            }
            if (preg_match('/' . $mapping['title'] . '/i', $label)) {
                if (empty($aggregatedResult[$key]['order'])) {
                    $aggregatedResult[$key]['order'] = $order;
                }
                array_push($aggregatedResult[$key]['time'], ...$time['times']);
                if (!in_array($label, $aggregatedResult[$key]['labels'])
                    && !$aggregatedResult[$key]['is_service_url']) {
                    array_push($aggregatedResult[$key]['labels'], $label);
                }
            }
        }
        $memoryUsage = [];
        foreach ($memoryUsageData as $row) {
            if (preg_match('/' . $mapping['uri'] . '/i', $row['uri'])) {
                $memoryUsage[] = $row['memory'];
            }
        }
        $aggregatedResult[$key]['memory'] = count($memoryUsage)
            ? round(calculate_average($memoryUsage) / 1024 / 1024, 2) : '-';
    }
    usort(
        $aggregatedResult,
        function ($a, $b) {
            return $a['order'] <=> $b['order'];
        }
    );
    return $aggregatedResult;
}

/**
 * Write aggregate report to the output file.
 *
 * @param array $aggregatedResult
 * @param int $executionTime
 * @param string $outputFile Path to the output report
 * @return void
 */
function parseReportAndWriteToCsv(array $aggregatedResult, $executionTime, $outputFile)
{
    $headersArray = [
        'Scenario',
        'Label',
        'JMeter Label',
        'Median elapsed time, ms',
        'Average elapsed time, ms',
        'Min elapsed time, ms',
        'Max elapsed time, ms',
        '95 percentile elapsed time, ms',
        '99 percentile elapsed time, ms',
        'Amount of hits per hour',
        'Memory Usage, Mb'
    ];
    $fp = fopen($outputFile, 'w');

    $pageViews = 0;
    $checkoutCount = 0;
    foreach ($aggregatedResult as $row) {
        if ($row['is_storefront']) {
            $pageViews += count($row['time']);
        }
        if (strpos($row['label'], 'Checkout Success Page') !== false) {
            $checkoutCount += count($row['time']);
        }
    }
    fputcsv($fp, ['Checkouts Per Hour:', round($checkoutCount / $executionTime * 3600000, 2)]);
    fputcsv($fp, ['Page Views Per Hour:', round($pageViews / $executionTime * 3600000, 2)]);
    fputcsv($fp, ['Test Duration, s:', round($executionTime / 1000)]);
    fputcsv($fp, ['']);
    fputcsv($fp, ['']);
    fputcsv($fp, $headersArray);
    foreach ($aggregatedResult as $row) {
        if (count($row['time']) && !$row['is_service_url']) {
            sort($row['time']);
            $ar = [
                $row['scenario'],
                $row['label'],
                implode("\n", $row['labels']),
                calculate_median($row['time']),
                calculate_average($row['time']),
                min($row['time']),
                max($row['time']),
                calculate_percentile($row['time'], 0.95),
                calculate_percentile($row['time'], 0.99),
                round(count($row['time']) / $executionTime * 3600000, 2),
                $row['memory']
            ];
            fputcsv($fp, $ar);
        }
    }
    fputcsv($fp, ['']);
    fputcsv($fp, ['']);
    foreach ($aggregatedResult as $row) {
        if (count($row['time']) && $row['is_service_url']) {
            sort($row['time']);
            $ar = [
                'Total',
                $row['label'],
                implode("\n", $row['labels']),
                calculate_median($row['time']),
                calculate_average($row['time']),
                min($row['time']),
                max($row['time']),
                calculate_percentile($row['time'], 0.95),
                calculate_percentile($row['time'], 0.99),
                round(count($row['time']) / $executionTime * 3600000, 2),
                $row['memory']
            ];
            fputcsv($fp, $ar);
        }
    }
    fputcsv($fp, ['']);
    fputcsv($fp, ['']);
    foreach ($aggregatedResult as $row) {
        if (count($row['time']) == 0) {
            $ar = [$row['scenario'], $row['label'], '-', '-', '-', '-', '-', '-', '-', 0, '-'];
            fputcsv($fp, $ar);
        }
    }
    fclose($fp);
}

/**
 * Calculate average value of elements in array.
 *
 * @param array $arr
 * @return int
 */
function calculate_average(array $arr)
{
    return (int)(array_sum($arr)/count($arr));
}

/**
 * Calculate median value of elements in array.
 *
 * @param array $arr
 * @return int
 */
function calculate_median(array $arr)
{
    $count = count($arr);
    $middleval = floor(($count - 1) / 2);
    if ($count % 2) {
        $median = $arr[$middleval];
    } else {
        $low = $arr[$middleval];
        $high = $arr[$middleval+1];
        $median = (($low + $high) / 2);
    }
    return (int)$median;
}

/**
 * Calculate percentile value of elements in array.
 *
 * @param array $arr
 * @param float $percentile From 0 to 1
 * @return int
 */
function calculate_percentile(array $arr, $percentile)
{
    $count = count($arr);
    $allindex = ($count - 1) * $percentile;
    $intvalindex = intval($allindex);
    $floatval = $allindex - $intvalindex;
    if (!is_float($floatval)) {
        $result = $arr[$intvalindex];
    } else {
        if ($count > $intvalindex + 1) {
            $result = $floatval * ($arr[$intvalindex + 1] - $arr[$intvalindex]) + $arr[$intvalindex];
        } else {
            $result = $arr[$intvalindex];
        }
    }
    return (int)$result;
}
