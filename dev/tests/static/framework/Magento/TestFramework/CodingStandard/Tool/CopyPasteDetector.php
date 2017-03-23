<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        exec($this->getCommand() . ' --version', $output, $exitCode);
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
        $blacklistedDirs = [];
        $blacklistedFileNames = [];
        foreach ($this->blacklist as $file) {
            $file = escapeshellarg(trim($file));
            if (!$file) {
                continue;
            }
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext != '') {
                $blacklistedFileNames[] = $file;
            } else {
                $blacklistedDirs[] = '--exclude ' . $file . ' ';
            }
        }

        $command = $this->getCommand() . ' --log-pmd ' . escapeshellarg($this->reportFile)
            . ' --names-exclude ' . join(',', $blacklistedFileNames) . ' --min-lines 13 ' . join(' ', $blacklistedDirs)
            . ' ' . implode(' ', $whiteList);
        exec($command, $output, $exitCode);

        return !(bool)$exitCode;
    }

    /**
     * Get PHPCPD command
     *
     * @return string
     */
    private function getCommand()
    {
        $vendorDir = require BP . '/app/etc/vendor_path.php';
        return 'php ' . BP . '/' . $vendorDir . '/bin/phpcpd';
    }
}
