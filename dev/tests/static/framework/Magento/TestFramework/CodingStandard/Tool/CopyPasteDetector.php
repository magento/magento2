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
    public function run(array $whiteList, array $blackList = array(), array $extensions = array())
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
