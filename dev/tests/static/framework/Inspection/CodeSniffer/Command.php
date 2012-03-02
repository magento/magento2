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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PHP Code Sniffer shell command
 */
class Inspection_CodeSniffer_Command extends Inspection_CommandAbstract
{
    /**
     * @var string
     */
    protected $_rulesetDir;

    /**
     * @var array
     */
    protected $_extensions = array();

    /**
     * Constructor
     *
     * @param string $rulesetDir Directory that locates the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($rulesetDir, $reportFile)
    {
        parent::__construct($reportFile);
        $this->_rulesetDir = $rulesetDir;
    }

    /**
     * Limit scanning folders by file extensions
     *
     * Array of alphanumeric strings, for example: 'php', 'xml', 'phtml', 'css'...
     *
     * @param array $extensions
     * @return Inspection_CodeSniffer_Command
     */
    public function setExtensions(array $extensions)
    {
        $this->_extensions = $extensions;
        return $this;
    }

    /**
     * @return string
     */
    public function _buildVersionShellCmd()
    {
        return 'phpcs --version';
    }

    /**
     * @param array $whiteList
     * @param array $blackList
     * @return string
     */
    protected function _buildShellCmd($whiteList, $blackList)
    {
        $whiteList = array_map('escapeshellarg', $whiteList);
        $whiteList = implode(' ', $whiteList);

        /* Note: phpcs allows regular expressions for the ignore list */
        $blackListStr = '';
        if ($blackList) {
            foreach ($blackList as $fileOrDir) {
                $fileOrDir = str_replace('/', DIRECTORY_SEPARATOR, $fileOrDir);
                $blackListStr .= ($blackListStr ? ',' : '') . preg_quote($fileOrDir);
            }
            $blackListStr = '--ignore=' . escapeshellarg($blackListStr);
        }

        return 'phpcs'
            . ($blackListStr ? ' ' . $blackListStr : '')
            . ' --standard=' . escapeshellarg($this->_rulesetDir)
            . ' --report=checkstyle'
            . ($this->_extensions ? ' --extensions=' . implode(',', $this->_extensions) : '')
            . ' --report-file=' . escapeshellarg($this->_reportFile)
            . ' -n'
            . ' ' . $whiteList
        ;
    }
}
