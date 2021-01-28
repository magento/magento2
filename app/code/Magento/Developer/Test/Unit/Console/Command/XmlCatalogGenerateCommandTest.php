<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\XmlCatalogGenerateCommand;
use Symfony\Component\Console\Tester\CommandTester;

class XmlCatalogGenerateCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var XmlCatalogGenerateCommand
     */
    private $command;

    public function testExecuteBadType()
    {
        $fixtureXmlFile = __DIR__ . '/_files/test.xml';

        $filesMock = $this->createPartialMock(\Magento\Framework\App\Utility\Files::class, ['getXmlCatalogFiles']);
        $filesMock->expects($this->at(0))
            ->method('getXmlCatalogFiles')
            ->willReturn([[$fixtureXmlFile]]);
        $filesMock->expects($this->at(1))
            ->method('getXmlCatalogFiles')
            ->willReturn([]);
        $urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with($this->equalTo('urn:magento:framework:Module/etc/module.xsd'))
            ->willReturn($fixtureXmlFile);

        $phpstormFormatMock = $this->createMock(\Magento\Developer\Model\XmlCatalog\Format\PhpStorm::class);
        $phpstormFormatMock->expects($this->once())
            ->method('generateCatalog')
            ->with(
                $this->equalTo(['urn:magento:framework:Module/etc/module.xsd' => $fixtureXmlFile]),
                $this->equalTo('test')
            )->willReturn(null);

        $formats = ['phpstorm' => $phpstormFormatMock];
        $readFactory = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $readDirMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);

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
}
