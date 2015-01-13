<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * PHP Copy Paste Detector v1.4.0 tool wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool;

class CopyPasteDetector implements \Magento\TestFramework\CodingStandard\ToolInterface
{
    /**
     * Report file
     *
     * @var string
     */
    protected $_reportFile;

    /**
     * Constructor
     *
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($reportFile)
    {
        $this->_reportFile = $reportFile;
    }

    /**
     * Whether the tool can be ran on the current environment
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @return bool
     */
    public function canRun()
    {
        exec('phpcpd --version', $output, $exitCode);
        return $exitCode === 0;
    }

    /**
     * Run tool for files specified
     *
     * @param array $whiteList Files/directories to be inspected
     * @param array $blackList Files/directories to be excluded from the inspection
     * @param array $extensions Array of alphanumeric strings, for example: 'php', 'xml', 'phtml', 'css'...
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     *
     * @return int
     */
    public function run(array $whiteList, array $blackList = [], array $extensions = [])
    {
        $blackListStr = ' ';
        foreach ($blackList as $file) {
            $file = escapeshellarg(trim($file));
            if (!$file) {
                continue;
            }
            $blackListStr .= '--exclude ' . $file . ' ';
        }

        $command = 'phpcpd' . ' --log-pmd ' . escapeshellarg(
            $this->_reportFile
        ) . ' --min-lines 13' . $blackListStr . ' ' . BP;

        exec($command, $output, $exitCode);

        return !(bool)$exitCode;
    }
}
