<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\XmlConverterCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Developer\Model\Tools\Formatter;

class XmlConverterCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Formatter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formatter;

    /**
     * @var XmlConverterCommand
     */
    private $command;

    /**
     * @var \DOMDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private $domXml;

    /**
     * @var \DOMDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private $domXsl;

    /**
     * @var \XSLTProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $xsltProcessor;

    public function setUp()
    {
        $this->formatter = $this->getMock('Magento\Developer\Model\Tools\Formatter', [], [], '', false);
        $this->domXml = $this->getMock('DOMDocument', [], [], '', false);
        $this->domXsl = $this->getMock('DOMDocument', [], [], '', false);
        $this->xsltProcessor = $this->getMock('XSLTProcessor', [], [], '', false);
        $this->command = new XmlConverterCommand($this->formatter, $this->domXml, $this->domXsl, $this->xsltProcessor);
    }

    public function testExecute()
    {
        $this->domXml->expects($this->once())->method('load')->with('file.xml');
        $this->domXsl->expects($this->once())->method('load')->with('file.xsl');

        $this->xsltProcessor->expects($this->once())->method('transformToXml')->with($this->domXml)->willReturn('XML');

        $this->formatter->expects($this->once())->method('format')->with('XML')->willReturn('result');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                XmlConverterCommand::XML_FILE_ARGUMENT => 'file.xml',
                XmlConverterCommand::PROCESSOR_ARGUMENT => 'file.xsl'
            ]
        );
        $this->assertContains('result', $commandTester->getDisplay());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Not enough arguments
     */
    public function testWrongParameter()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }
}
