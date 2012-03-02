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

class Inspection_CodeSniffer_CommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Inspection_CodeSniffer_Command|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cmd;

    protected function setUp()
    {
        $this->_cmd = $this->getMock(
            'Inspection_CodeSniffer_Command',
            array('_execShellCmd'),
            array('some/ruleset/dir', 'some/report/file.xml')
        );
    }

    /**
     * @dataProvider canRunDataProvider
     */
    public function testCanRun($cmdOutput, $expectedResult)
    {
        $this->_cmd
            ->expects($this->once())
            ->method('_execShellCmd')
            ->with($this->stringContains('phpcs'))
            ->will($this->returnValue($cmdOutput))
        ;
        $this->assertEquals($expectedResult, $this->_cmd->canRun());
    }

    public function canRunDataProvider()
    {
        return array(
            'success' => array('PHP_CodeSniffer version X.Y.Z', true),
            'failure' => array(false, false),
        );
    }

    /**
     * @dataProvider getVersionDataProvider
     */
    public function testGetVersion($versionCmdOutput, $expectedVersion)
    {
        $this->_cmd
            ->expects($this->once())
            ->method('_execShellCmd')
            ->with($this->stringContains('phpcs'))
            ->will($this->returnValue($versionCmdOutput))
        ;
        $this->assertEquals($expectedVersion, $this->_cmd->getVersion());
    }

    public function getVersionDataProvider()
    {
        return array(
            array('PHP_CodeSniffer version 1.3.0RC2 (beta) by Squiz Pty Ltd. (http://www.squiz.net)', '1.3.0RC2'),
            array('PHP_CodeSniffer version 1.3.0 (stable) by Squiz Pty Ltd. (http://www.squiz.net)',  '1.3.0'),
        );
    }

    public function testRun()
    {
        $expectedQuoteChar = substr(escapeshellarg(' '), 0, 1);
        $expectedCmd = 'phpcs'
            . ' --standard="some/ruleset/dir"'
            . ' --report=checkstyle'
            . ' --report-file="some/report/file.xml"'
            . ' -n'
            . ' "some/test/dir with space"'
            . ' "some/test/file with space.php"'
        ;
        $expectedCmd = str_replace('"', $expectedQuoteChar, $expectedCmd);
        $this->_cmd
            ->expects($this->once())
            ->method('_execShellCmd')
            ->with($expectedCmd)
        ;
        $this->_cmd->run(array('some/test/dir with space', 'some/test/file with space.php'));
    }

    /**
     * @depends testRun
     */
    public function testRunWithExtensions()
    {
        $this->assertSame($this->_cmd, $this->_cmd->setExtensions(array('txt', 'xml')));
        $this->_cmd
            ->expects($this->once())
            ->method('_execShellCmd')
            ->with($this->stringContains(' --extensions=txt,xml '))
        ;
        $this->_cmd->run(array());
    }
}
