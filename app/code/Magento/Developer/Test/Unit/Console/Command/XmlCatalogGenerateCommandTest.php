<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\XmlCatalogGenerateCommand;
use Magento\Developer\Model\XmlCatalog\Format\PhpStorm;
use Magento\Developer\Model\XmlCatalog\Format\VsCode;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class XmlCatalogGenerateCommandTest extends TestCase
{
    /**
     * @var XmlCatalogGenerateCommand
     */
    private $command;

    public function testExecuteBadType()
    {
        $fixtureXmlFile = __DIR__ . '/_files/test.xml';

        $filesMock = $this->createPartialMock(Files::class, ['getXmlCatalogFiles']);
        $filesMock->expects($this->at(0))
            ->method('getXmlCatalogFiles')
            ->willReturn([[$fixtureXmlFile]]);
        $filesMock->expects($this->at(1))
            ->method('getXmlCatalogFiles')
            ->willReturn([]);
        $urnResolverMock = $this->createMock(UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with($this->equalTo('urn:magento:framework:Module/etc/module.xsd'))
            ->willReturn($fixtureXmlFile);

        $phpstormFormatMock = $this->createMock(PhpStorm::class);
        $phpstormFormatMock->expects($this->once())
            ->method('generateCatalog')
            ->with(
                $this->equalTo(['urn:magento:framework:Module/etc/module.xsd' => $fixtureXmlFile]),
                $this->equalTo('test')
            )->willReturn(null);

        $formats = ['phpstorm' => $phpstormFormatMock];
        $readFactory = $this->createMock(ReadFactory::class);
        $readDirMock = $this->getMockForAbstractClass(ReadInterface::class);

        $content = file_get_contents($fixtureXmlFile);

        $readDirMock->expects($this->once())
            ->method('readFile')
            ->with($this->equalTo('test.xml'))
            ->willReturn($content);
        $readFactory->expects($this->once())
            ->method('create')
            ->willReturn($readDirMock);

        $this->command = new XmlCatalogGenerateCommand(
            $filesMock,
            $urnResolverMock,
            $readFactory,
            $formats
        );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([XmlCatalogGenerateCommand::IDE_FILE_PATH_ARGUMENT => 'test']);
        $this->assertEquals('', $commandTester->getDisplay());
    }

    public function testExecuteVsCodeFormat()
    {
        $fixtureXmlFile = __DIR__ . '/_files/test.xml';

        $filesMock = $this->createPartialMock(Files::class, ['getXmlCatalogFiles']);
        $filesMock->expects($this->at(0))
            ->method('getXmlCatalogFiles')
            ->willReturn([[$fixtureXmlFile]]);
        $filesMock->expects($this->at(1))
            ->method('getXmlCatalogFiles')
            ->willReturn([]);
        $urnResolverMock = $this->createMock(UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with($this->equalTo('urn:magento:framework:Module/etc/module.xsd'))
            ->willReturn($fixtureXmlFile);

        $vscodeFormatMock = $this->createMock(VsCode::class);
        $vscodeFormatMock->expects($this->once())
            ->method('generateCatalog')
            ->with(
                $this->equalTo(['urn:magento:framework:Module/etc/module.xsd' => $fixtureXmlFile]),
                $this->equalTo('test')
            );

        $formats = ['vscode' => $vscodeFormatMock];
        $readFactory = $this->createMock(ReadFactory::class);
        $readDirMock = $this->getMockForAbstractClass(ReadInterface::class);

        $content = file_get_contents($fixtureXmlFile);

        $readDirMock->expects($this->once())
            ->method('readFile')
            ->with($this->equalTo('test.xml'))
            ->willReturn($content);
        $readFactory->expects($this->once())
            ->method('create')
            ->willReturn($readDirMock);

        $this->command = new XmlCatalogGenerateCommand(
            $filesMock,
            $urnResolverMock,
            $readFactory,
            $formats
        );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            '--' . XmlCatalogGenerateCommand::IDE_OPTION => 'vscode',
            XmlCatalogGenerateCommand::IDE_FILE_PATH_ARGUMENT => 'test',
        ]);
        $this->assertEquals('', $commandTester->getDisplay());
    }
}
