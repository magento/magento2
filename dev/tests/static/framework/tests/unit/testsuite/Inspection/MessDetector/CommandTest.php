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

class Inspection_MessDetector_CommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Inspection_MessDetector_Command|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cmd;

    protected function setUp()
    {
        $this->_cmd = $this->getMock(
            'Inspection_MessDetector_Command',
            array('_execShellCmd'),
            array('some/ruleset/file.xml', 'some/report/file.xml')
        );
    }

    public function testGetRulesetFile()
    {
        $this->assertEquals('some/ruleset/file.xml', $this->_cmd->getRulesetFile());
    }

    /**
     * @dataProvider canTestDataProvider
     */
    public function testCanRun($cmdOutput, $expectedResult)
    {
        $this->_cmd
            ->expects($this->once())
            ->method('_execShellCmd')
            ->with($this->stringContains('phpmd'))
            ->will($this->returnValue($cmdOutput))
        ;
        $this->assertEquals($expectedResult, $this->_cmd->canRun());
    }

    public function canTestDataProvider()
    {
        return array(
            'success' => array('PHPMD X.Y.Z', true),
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
            ->with($this->stringContains('phpmd'))
            ->will($this->returnValue($versionCmdOutput))
        ;
        $this->assertEquals($expectedVersion, $this->_cmd->getVersion());
    }

    public function getVersionDataProvider()
    {
        return array(
            array('PHPMD 0.2.8RC1 by Manuel Pichler', '0.2.8RC1'),
            array('PHPMD 1.1.1 by Manuel Pichler',    '1.1.1'),
        );
    }

    public function testRun()
    {
        $expectedQuoteChar = substr(escapeshellarg(' '), 0, 1);
        $expectedCmd = 'phpmd'
            . ' "some/test/dir with space,some/test/file with space.php"'
            . ' xml'
            . ' "some/ruleset/file.xml"'
            . ' --reportfile "some/report/file.xml"'
        ;
        $expectedCmd = str_replace('"', $expectedQuoteChar, $expectedCmd);
        $this->_cmd
            ->expects($this->once())
            ->method('_execShellCmd')
            ->with($expectedCmd)
        ;
        $this->_cmd->run(array('some/test/dir with space', 'some/test/file with space.php'));
    }
}
