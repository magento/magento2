<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * PHP Code Mess v1.3.3 tool wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool;

class CodeMessDetector implements \Magento\TestFramework\CodingStandard\ToolInterface
{
    /**
     * Ruleset directory
     *
     * @var string
     */
    protected $_rulesetFile;

    /**
     * Report file
     *
     * @var string
     */
    protected $_reportFile;

    /**
     * Constructor
     *
     * @param string $rulesetDir \Directory that locates the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($rulesetFile, $reportFile)
    {
        $this->_reportFile = $reportFile;
        $this->_rulesetFile = $rulesetFile;
    }

    /**
     * Whether the tool can be ran on the current environment
     *
     * @return bool
     */
    public function canRun()
    {
        return class_exists('PHP_PMD_TextUI_Command');
    }

    /**
     * Run tool for files specified
     *
     * @param array $whiteList Files/directories to be inspected
     * @param array $blackList Files/directories to be excluded from the inspection
     * @param array $extensions Array of alphanumeric strings, for example: 'php', 'xml', 'phtml', 'css'...
     *
     * @return int
     */
    public function run(array $whiteList, array $blackList = [], array $extensions = [])
    {
        $commandLineArguments = [
            'run_file_mock', //emulate script name in console arguments
            implode(',', $whiteList),
            'xml', //report format
            $this->_rulesetFile,
            '--exclude',
            implode(',', $blackList),
            '--reportfile',
            $this->_reportFile,
        ];

        $options = new \PHP_PMD_TextUI_CommandLineOptions($commandLineArguments);

        $command = new \PHP_PMD_TextUI_Command();

        return $command->run($options, new \PHP_PMD_RuleSetFactory());
    }
}
