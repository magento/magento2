<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Console\Command\GenerateVclCommand;
use Magento\PageCache\Model\Varnish\VclGenerator;
use Magento\PageCache\Model\VclGeneratorInterfaceFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateVclCommandInputOptionTest extends TestCase
{
    /**
     * @var GenerateVclCommand
     */
    private $command;

    /**
     * @var VclGeneratorInterfaceFactory
     */
    private $vclGeneratorInterfaceFactory;

    /**
     * @var WriteFactory
     */
    private $writeFactoryMock;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var VclGenerator
     */
    private $vclGenerator;

    /**
     * @var Json
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->vclGeneratorInterfaceFactory = $this->createMock(VclGeneratorInterfaceFactory::class);
        $this->vclGenerator = $this->createMock(VclGenerator::class);
        $this->vclGenerator->method('generateVcl')->willReturn('test.vcl" file can\'t be read.');
        $this->vclGeneratorInterfaceFactory->method('create')->willReturn($this->vclGenerator);
        $this->writeFactoryMock = $this->createMock(WriteFactory::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->serializer = $this->getMockBuilder(Json::class)
            ->onlyMethods(['unserialize'])
            ->getMockForAbstractClass();

        $this->command = new GenerateVclCommand(
            $this->vclGeneratorInterfaceFactory,
            $this->writeFactoryMock,
            $this->scopeConfigMock,
            $this->serializer
        );
    }

    public function testConfigure()
    {
        $this->assertEquals('varnish:vcl:generate', $this->command->getName());
        $this->assertEquals(
            'Generates Varnish VCL and echos it to the command line',
            $this->command->getDescription()
        );
    }

    public function testInputOption()
    {
        $options = [
            '--' . GenerateVclCommand::INPUT_FILE_OPTION => 'test.vcl',
            '--' . GenerateVclCommand::EXPORT_VERSION_OPTION => 6,
        ];

        $commandTester = new CommandTester($this->command);
        $commandTester->execute($options, ['interactive' => false]);
        $this->assertStringContainsString(
            'test.vcl" file can\'t be read.',
            $commandTester->getDisplay()
        );
    }
}
