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
 * @package     Magento
 * @subpackage  static_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PHP Code Sniffer tool wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool;

class CodeSniffer
    implements \Magento\TestFramework\CodingStandard\ToolInterface
{
    /**
     * Ruleset directory
     *
     * @var string
     */
    protected $_rulesetDir;

    /**
     * Report file
     *
     * @var string
     */
    protected $_reportFile;

    /**
     * PHPCS cli tool wrapper
     *
     * @var \Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper
     */
    protected $_wrapper;

    /**
     * Constructor
     *
     * @param string $rulesetDir \Directory that locates the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     * @param \Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper $wrapper
     */
    public function __construct($rulesetDir, $reportFile,
        \Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper $wrapper
    ) {
        $this->_reportFile = $reportFile;
        $this->_rulesetDir = $rulesetDir;
        $this->_wrapper = $wrapper;
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
        return $this->_wrapper->version();
    }

    /**
     * Run tool for files cpecified
     *
     * @param array $whiteList Files/directories to be inspected
     * @param array $blackList Files/directories to be excluded from the inspection
     * @param array $extensions Array of alphanumeric strings, for example: 'php', 'xml', 'phtml', 'css'...
     *
     * @return int
     */
    public function run(array $whiteList, array $blackList = array(), array $extensions = array())
    {
        $whiteList = array_map(function ($item) {
            return str_replace('/', DIRECTORY_SEPARATOR, $item);
        }, $whiteList);

        $blackList = array_map(function ($item) {
            return preg_quote(str_replace('/', DIRECTORY_SEPARATOR, $item));
        }, $blackList);

        $this->_wrapper->checkRequirements();
        $settings = $this->_wrapper->getDefaults();
        $settings['files'] = $whiteList;
        $settings['standard'] = $this->_rulesetDir;
        $settings['ignored'] = $blackList;
        $settings['extensions'] = $extensions;
        $settings['reportFile'] = $this->_reportFile;
        $settings['warningSeverity'] = 0;
        $settings['reports']['checkstyle'] = null;
        $this->_wrapper->setValues($settings);

        ob_start();
        $result = $this->_wrapper->process();
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
        return $this->_reportFile;
    }
}
