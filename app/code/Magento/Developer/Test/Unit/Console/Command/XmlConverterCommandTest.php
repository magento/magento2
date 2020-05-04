<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\XmlConverterCommand;
use Magento\Developer\Model\Tools\Formatter;
use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\XsltProcessor\XsltProcessorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class XmlConverterCommandTest extends TestCase
{
    /**
     * @var Formatter|MockObject
     */
    private $formatter;

    /**
     * @var XmlConverterCommand
     */
    private $command;

    /**
     * @var DomDocumentFactory|MockObject
     */
    private $domFactory;

    /**
     * @var XsltProcessorFactory|MockObject
     */
    private $xsltProcessorFactory;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->formatter = $this->createMock(Formatter::class);
        $this->domFactory = $this->createMock(DomDocumentFactory::class);
        $this->xsltProcessorFactory = $this->createMock(XsltProcessorFactory::class);

        $this->command = new XmlConverterCommand($this->formatter, $this->domFactory, $this->xsltProcessorFactory);
    }

    public function testExecute()
    {
        $domXml = $this->createMock(\DOMDocument::class);
        $domXsl = clone $domXml;
        $domXml->expects($this->once())->method('load')->with('file.xml');
        $domXsl->expects($this->once())->method('load')->with('file.xsl');

        $this->domFactory->expects($this->at(0))->method('create')->willReturn($domXml);
        $this->domFactory->expects($this->at(1))->method('create')->willReturn($domXsl);

        $xsltProcessor = $this->createMock(\XSLTProcessor::class);
        $xsltProcessor->expects($this->once())->method('transformToXml')->with($domXml)->willReturn('XML');

        $this->xsltProcessorFactory->expects($this->once())->method('create')->willReturn($xsltProcessor);

        $this->formatter->expects($this->once())->method('format')->with('XML')->willReturn('result');

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(
            [
                XmlConverterCommand::XML_FILE_ARGUMENT => 'file.xml',
                XmlConverterCommand::PROCESSOR_ARGUMENT => 'file.xsl'
            ]
        );
        $this->assertStringContainsString('result', $commandTester->getDisplay());
    }

    public function testWrongParameter()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Not enough arguments');
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }
}
