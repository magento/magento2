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
 * PHP Mess Detector shell command
 */
class Inspection_MessDetector_Command extends Inspection_CommandAbstract
{
    /**
     * @var string
     */
    protected $_rulesetFile;

    /**
     * Constructor
     *
     * @param string $rulesetFile File that declares the inspection rules
     * @param string $reportFile Destination file to write inspection report to
     */
    public function __construct($rulesetFile, $reportFile)
    {
        parent::__construct($reportFile);
        $this->_rulesetFile = $rulesetFile;
    }

    /**
     * Get path to the ruleset file
     *
     * @return string
     */
    public function getRulesetFile()
    {
        return $this->_rulesetFile;
    }

    /**
     * @return string
     */
    public function _buildVersionShellCmd()
    {
        return 'phpmd --version';
    }

    /**
     * @param array $whiteList
     * @param array $blackList
     * @return string
     */
    protected function _buildShellCmd($whiteList, $blackList)
    {
        $whiteList = implode(',', $whiteList);
        $whiteList = escapeshellarg($whiteList);

        $blackListStr = '';
        if ($blackList) {
            foreach ($blackList as $fileOrDir) {
                $fileOrDir = str_replace('/', DIRECTORY_SEPARATOR, $fileOrDir);
                $blackListStr .= ($blackListStr ? ',' : '') . $fileOrDir;
            }
            $blackListStr = '--exclude ' . escapeshellarg($blackListStr);
        }

        return 'phpmd'
            . ' ' . $whiteList
            . ' xml'
            . ' ' . escapeshellarg($this->_rulesetFile)
            . ($blackListStr ? ' ' . $blackListStr : '')
            . ' --reportfile ' . escapeshellarg($this->_reportFile)
        ;
    }
}
