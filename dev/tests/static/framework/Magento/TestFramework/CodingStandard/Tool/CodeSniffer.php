<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * PHP Code Sniffer tool wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool;

use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use Magento\TestFramework\CodingStandard\ToolInterface;

class CodeSniffer implements ToolInterface
{
    /**
     * Ruleset directory
     *
     * @var string
     */
    protected $rulesetDir;

    /**
     * Report file
     *
     * @var string
     */
    protected $reportFile;

    /**
     * PHPCS cli tool wrapper
     *
     * @var Wrapper
     */
    protected $wrapper;

    /**
     * Constructor
     *
     * @param string $rulesetDir \Directory that locates the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     * @param Wrapper $wrapper
     */
    public function __construct($rulesetDir, $reportFile, Wrapper $wrapper)
    {
        $this->reportFile = $reportFile;
        $this->rulesetDir = $rulesetDir;
        $this->wrapper = $wrapper;
    }

    /**
     * Whether the tool can be ran on the current environment
     *
     * @return bool
     */
    public function canRun()
    {
        return class_exists('PHP_CodeSniffer_CLI');
    }

    /**
     * Return the version of code sniffer found
     *
     * @return string
     */
    public function version()
    {
        return $this->wrapper->version();
    }

    /**
     * Run tool for files specified
     *
     * @param array $whiteList Files/directories to be inspected
     * @param array $blackList Files/directories to be excluded from the inspection
     * @param array $extensions Array of alphanumeric strings, for example: 'php', 'xml', 'phtml', 'css'...
     * @param int $warningSeverity Severity level of warnings, default is 0
     *
     * @return int
     */
    public function run(
        array $whiteList,
        array $blackList = [],
        array $extensions = [],
        $warningSeverity = 0
    ) {
        $whiteList = array_map(
            function ($item) {
                return $item;
            },
            $whiteList
        );

        $blackList = array_map(
            function ($item) {
                return preg_quote($item);
            },
            $blackList
        );

        $this->wrapper->checkRequirements();
        $settings = $this->wrapper->getDefaults();
        $settings['files'] = $whiteList;
        $settings['standard'] = [$this->rulesetDir];
        $settings['ignored'] = $blackList;
        $settings['extensions'] = $extensions;
        $settings['reportFile'] = $this->reportFile;
        $settings['warningSeverity'] = $warningSeverity;
        $settings['reports']['checkstyle'] = null;
        $this->wrapper->setValues($settings);

        ob_start();
        $result = $this->wrapper->process();
        ob_end_clean();

        return $result;
    }

    /**
     * Get report file
     *
     * @return string
     */
    public function getReportFile()
    {
        return $this->reportFile;
    }
}
