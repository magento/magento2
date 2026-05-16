<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Developer\Test\Unit\Console\Command;

use Magento\Developer\Console\Command\TemplateHintsDisableCommand;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\Cli;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TemplateHintsDisableCommandTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $resourceConfig;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    /**
     * @var ConfigPathResolver|MockObject
     */
    private $configPathResolver;

    /**
     * @var TemplateHintsDisableCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->resourceConfig = $this->createMock(ConfigInterface::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->configPathResolver = $this->createMock(ConfigPathResolver::class);

        $this->configPathResolver->method('resolve')
            ->willReturn('system/default/dev/debug/template_hints_storefront');

        $this->command = new TemplateHintsDisableCommand(
            $this->resourceConfig,
            $this->deploymentConfig,
            $this->configPathResolver
        );
    }

    public function testExecuteSuccess(): void
    {
        $this->deploymentConfig->method('get')->willReturn(null);

        $this->resourceConfig->expects($this->once())
            ->method('saveConfig')
            ->with('dev/debug/template_hints_storefront', 0, 'default', 0);

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with('<info>Template hints disabled. Refresh cache types</info>');

        $this->assertEquals(Cli::RETURN_SUCCESS, $this->command->run($input, $output));
    }

    public function testExecuteFailsWhenConfigIsLocked(): void
    {
        $this->deploymentConfig->method('get')->willReturn('1');

        $this->resourceConfig->expects($this->never())->method('saveConfig');

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('already been locked'));

        $this->assertEquals(Cli::RETURN_FAILURE, $this->command->run($input, $output));
    }

    public function testConstructorKeepsAdditionalDependenciesOptional(): void
    {
        $constructor = new ReflectionMethod(TemplateHintsDisableCommand::class, '__construct');

        $this->assertSame(1, $constructor->getNumberOfRequiredParameters());
    }
}
