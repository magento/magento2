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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function run(array $whiteList, array $blackList = array(), array $extensions = array())
    {
        $commandLineArguments = array(
            'run_file_mock', //emulate script name in console arguments
            implode(',', $whiteList),
            'xml', //report format
            $this->_rulesetFile,
            '--exclude',
            implode(',', $blackList),
            '--reportfile',
            $this->_reportFile
        );

        $options = new \PHP_PMD_TextUI_CommandLineOptions($commandLineArguments);

        $command = new \PHP_PMD_TextUI_Command();

        return $command->run($options, new \PHP_PMD_RuleSetFactory());
    }
}
