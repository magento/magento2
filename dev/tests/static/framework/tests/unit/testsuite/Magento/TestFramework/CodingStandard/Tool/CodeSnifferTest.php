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
namespace Magento\TestFramework\CodingStandard\Tool;

class CodeSnifferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\CodingStandard\Tool\CodeSniffer
     */
    protected $_tool;

    /**
     * @var PHP_CodeSniffer_CLI
     */
    protected $_wrapper;

    /**
     * Rule set directory
     */
    const RULE_SET = 'some/ruleset/directory';

    /**
     * Report file
     */
    const REPORT_FILE = 'some/report/file.xml';

    protected function setUp()
    {
        $this->_wrapper = $this->getMock('Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper');
        $this->_tool = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer(
            self::RULE_SET,
            self::REPORT_FILE,
            $this->_wrapper
        );
    }

    public function testRun()
    {
        $whiteList = array('test' . rand(), 'test' . rand());
        $blackList = array('test' . rand(), 'test' . rand());
        $extensions = array('test' . rand(), 'test' . rand());

        $this->_wrapper->expects($this->once())->method('getDefaults')->will($this->returnValue(array()));

        $expectedCliEmulation = array(
            'files' => $whiteList,
            'standard' => [self::RULE_SET],
            'ignored' => $blackList,
            'extensions' => $extensions,
            'reportFile' => self::REPORT_FILE,
            'warningSeverity' => 0,
            'reports' => array('checkstyle' => null)
        );

        $this->_wrapper->expects($this->once())->method('setValues')->with($this->equalTo($expectedCliEmulation));

        $this->_wrapper->expects($this->once())->method('process');

        $this->_tool->run($whiteList, $blackList, $extensions);
    }

    public function testGetReportFile()
    {
        $this->assertEquals(self::REPORT_FILE, $this->_tool->getReportFile());
    }
}
