<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command;

use Magento\Config\Console\Command\ConfigShowCommand;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\App\Scope\ValidatorInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\ConfigPathResolver;

class ConfigShowCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValidatorInterface|MockObject
     */
    private $scopeValidatorMock;

    /**
     * @var ConfigSourceInterface|MockObject
     */
    private $configSourceMock;

    /**
     * @var MetadataProcessor|MockObject
     */
    private $metadataProcessorMock;

    /**
     * @var ConfigPathResolver|MockObject
     */
    private $pathResolverMock;

    /**
     * @var ConfigShowCommand
     */
    private $command;

    protected function setUp()
    {
        $this->metadataProcessorMock = $this->getMockBuilder(MetadataProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pathResolverMock = $this->getMockBuilder(ConfigPathResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeValidatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->configSourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();

        $this->command = new ConfigShowCommand(
            $this->scopeValidatorMock,
            $this->configSourceMock,
            $this->pathResolverMock,
            $this->metadataProcessorMock
        );
    }

    public function testExecute()
    {
        $configPath = 'some/config/path';
        $resolvedConfigPath = 'someScope/someScopeCode/some/config/path';
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';

        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($scope, $scopeCode)
            ->willReturn(true);
        $this->pathResolverMock->expects($this->once())
            ->method('resolve')
            ->with($configPath, $scope, $scopeCode)
            ->willReturn($resolvedConfigPath);
        $this->configSourceMock->expects($this->once())
            ->method('get')
            ->with($resolvedConfigPath)
            ->willReturn('someValue');
        $this->metadataProcessorMock->expects($this->once())
            ->method('processValue')
            ->with('someValue')
            ->willReturn('someProcessedValue');

        $tester = $this->getConfigShowCommandTester($configPath, $scope, $scopeCode);

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $tester->getStatusCode()
        );
        $this->assertContains(
            'someProcessedValue',
            $tester->getDisplay()
        );
    }

    public function testNotValidScopeOrScopeCode()
    {
        $configPath = 'some/config/path';
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';

        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($scope, $scopeCode)
            ->willThrowException(new LocalizedException(__('error message')));

        $tester = $this->getConfigShowCommandTester($configPath, $scope, $scopeCode);

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertContains(
            __('error message')->render(),
            $tester->getDisplay()
        );
    }

    public function testConfigPathNotExist()
    {
        $configPath = 'some/path';
        $tester = $this->getConfigShowCommandTester($configPath);

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertContains(
            __('Configuration for path: "%1" doesn\'t exist', $configPath)->render(),
            $tester->getDisplay()
        );
    }

    /**
     * @param string $configPath
     * @param null|string $scope
     * @param null|string $scopeCode
     * @return CommandTester
     */
    private function getConfigShowCommandTester($configPath, $scope = null, $scopeCode = null)
    {
        $arguments = [
            ConfigShowCommand::INPUT_ARGUMENT_PATH => $configPath
        ];

        if ($scope !== null) {
            $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE] = $scope;
        }
        if ($scopeCode !== null) {
            $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE_CODE] = $scopeCode;
        }

        $tester = new CommandTester($this->command);
        $tester->execute($arguments);

        return $tester;
    }
}
