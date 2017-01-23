<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command;

use Magento\Config\Console\Command\ConfigShowCommand;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigShowCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValidatorInterface|MockObject
     */
    private $scopeValidatorMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $appConfigMock;

    /**
     * @var ConfigShowCommand
     */
    private $command;

    protected function setUp()
    {
        $this->appConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->scopeValidatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();

        $this->command = new ConfigShowCommand(
            $this->appConfigMock,
            $this->scopeValidatorMock
        );
    }

    public function testExecute()
    {
        $configPath = 'some/config/path';
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';

        $this->appConfigMock->expects($this->once())
            ->method('getValue')
            ->with($configPath, $scope, $scopeCode)
            ->willReturn('someValue');

        $tester = $this->getConfigShowCommandTester($configPath, $scope, $scopeCode);

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $tester->getStatusCode()
        );
        $this->assertContains(
            'someValue',
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
     * @param $configPath
     * @param mixed $scope
     * @param mixed $scopeCode
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
