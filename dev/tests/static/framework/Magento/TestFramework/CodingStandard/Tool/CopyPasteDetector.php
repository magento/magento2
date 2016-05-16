<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * PHP Copy Paste Detector v1.4.0 tool wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool;

use Magento\TestFramework\CodingStandard\ToolInterface;

class CopyPasteDetector implements ToolInterface, BlacklistInterface
{
    /**
     * Report file
     *
     * @var string
     */
    private $reportFile;

    /**
     * List of paths to be excluded from tool run
     *
     * @var array
     */
    private $blacklist;

    /**
     * Constructor
     *
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($reportFile)
    {
        $this->reportFile = $reportFile;
    }

    /**
     * {@inheritdoc}
     */
    public function setBlackList(array $blackList)
    {
        $this->blacklist = $blackList;
    }

    /**
     * Whether the tool can be run in the current environment
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @return bool
     */
    public function canRun()
    {
        $vendorDir = require BP . '/app/etc/vendor_path.php';
        exec('php ' . BP . '/' . $vendorDir . '/bin/phpcpd --version', $output, $exitCode);
        return $exitCode === 0;
    }

    /**
     * Run tool for files specified
     *
     * @param array $whiteList Files/directories to be inspected
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function run(array $whiteList)
    {
        $blackListStr = ' ';
        foreach ($this->blacklist as $file) {
            $file = escapeshellarg(trim($file));
            if (!$file) {
                continue;
            }
            $blackListStr .= '--exclude ' . $file . ' ';
        }

        $vendorDir = require BP . '/app/etc/vendor_path.php';
        $command = 'php ' . BP . '/' . $vendorDir . '/bin/phpcpd' . ' --log-pmd ' . escapeshellarg(
                $this->reportFile
            ) . ' --names-exclude "*Test.php" --min-lines 13' . $blackListStr . ' ' . implode(' ', $whiteList);

        exec($command, $output, $exitCode);

        return !(bool)$exitCode;
    }
}
