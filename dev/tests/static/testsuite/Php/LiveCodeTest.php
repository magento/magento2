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
 * @category    tests
 * @package     static
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Set of tests for static code analysis, e.g. code style, code complexity, copy paste detecting, etc.
 */
class Php_LiveCodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_reportDir = '';

    /**
     * @var array
     */
    protected static $_whiteList = array();

    /**
     * @var array
     */
    protected static $_blackList = array();

    public static function setUpBeforeClass()
    {
        self::$_reportDir = Utility_Files::init()->getPathToSource() . '/dev/tests/static/report';
        if (!is_dir(self::$_reportDir)) {
            mkdir(self::$_reportDir, 0777);
        }
        self::$_whiteList = self::_readLists(__DIR__ .'/_files/whitelist/*.txt');
        self::$_blackList = self::_readLists(__DIR__ .'/_files/blacklist/*.txt');
    }

    public function testCodeStyle()
    {
        $reportFile = self::$_reportDir . '/phpcs_report.xml';
        $cmd = new Inspection_CodeSniffer_Command(realpath(__DIR__ . '/_files/phpcs'), $reportFile);
        if (!$cmd->canRun()) {
            $this->markTestSkipped('PHP Code Sniffer command is not available.');
        }
        $cmd->setExtensions(array('php', 'phtml'));
        $this->assertTrue($cmd->run(self::$_whiteList, self::$_blackList), $cmd->getLastRunMessage());
    }

    public function testCodeMess()
    {
        $reportFile = self::$_reportDir . '/phpmd_report.xml';
        $cmd = new Inspection_MessDetector_Command(realpath(__DIR__ . '/_files/phpmd/ruleset.xml'), $reportFile);
        if (!$cmd->canRun()) {
            $this->markTestSkipped('PHP Mess Detector command line is not available.');
        }
        $this->assertTrue($cmd->run(self::$_whiteList, self::$_blackList), $cmd->getLastRunMessage());
    }

    public function testCopyPaste()
    {
        $reportFile = self::$_reportDir . '/phpcpd_report.xml';
        $cmd = new Inspection_CopyPasteDetector_Command($reportFile);
        if (!$cmd->canRun()) {
            $this->markTestSkipped('PHP Copy/Paste Detector command line is not available.');
        }
        $this->assertTrue($cmd->run(self::$_whiteList, self::$_blackList), $cmd->getLastRunMessage());
    }

    /**
     * Read all text files by specified glob pattern and combine them into an array of valid files/directories
     *
     * The Magento root path is prepended to all (non-empty) entries
     *
     * @param string $globPattern
     * @return array
     */
    protected static function _readLists($globPattern)
    {
        $result = array();
        foreach (glob($globPattern) as $list) {
            $result = array_merge($result, file($list));
        }
        $map = function($value) {
            return trim($value) ? Utility_Files::init()->getPathToSource() . '/' . trim($value) : '';
        };
        return array_filter(array_map($map, $result), 'file_exists');
    }
}
