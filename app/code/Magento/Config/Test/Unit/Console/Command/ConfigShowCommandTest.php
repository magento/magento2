<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Config\Test\Unit\Console\Command;

use Magento\Config\Console\Command\ConfigShow\ValueProcessor;
use Magento\Config\Console\Command\ConfigShowCommand;
use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Config\Console\Command\LocaleEmulatorInterface;
use Magento\Config\Model\Config\PathValidator;
use Magento\Config\Model\Config\PathValidatorFactory;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test for \Magento\Config\Console\Command\ConfigShowCommand.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigShowCommandTest extends TestCase
{
    private const CONFIG_PATH = 'some/config/path';
    private const SCOPE = 'some/config/path';
    private const SCOPE_CODE = 'someScopeCode';

    /**
     * @var ConfigShowCommand
     */
    private $model;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $scopeValidatorMock;

    /**
     * @var ConfigSourceInterface|MockObject
     */
    private $configSourceMock;

    /**
     * @var ValueProcessor|MockObject
     */
    private $valueProcessorMock;

    /**
     * @var ConfigPathResolver|MockObject
     */
    private $pathResolverMock;

    /**
     * @var EmulatedAdminhtmlAreaProcessor|MockObject
     */
    private $emulatedAreProcessorMock;

    /**
     * @var PathValidator|MockObject
     */
    private $pathValidatorMock;

    /**
     * @var LocaleEmulatorInterface|MockObject
     */
    private $localeEmulatorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->valueProcessorMock = $this->getMockBuilder(ValueProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pathResolverMock = $this->getMockBuilder(ConfigPathResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeValidatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->configSourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();
        $this->pathValidatorMock = $this->getMockBuilder(PathValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pathValidatorFactoryMock = $this->getMockBuilder(PathValidatorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pathValidatorFactoryMock->expects($this->atMost(1))
            ->method('create')
            ->willReturn($this->pathValidatorMock);

        $this->emulatedAreProcessorMock = $this->getMockBuilder(EmulatedAdminhtmlAreaProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeEmulatorMock = $this->getMockBuilder(LocaleEmulatorInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            ConfigShowCommand::class,
            [
                'scopeValidator' => $this->scopeValidatorMock,
                'configSource' => $this->configSourceMock,
                'pathResolver' => $this->pathResolverMock,
                'valueProcessor' => $this->valueProcessorMock,
                'pathValidatorFactory' => $pathValidatorFactoryMock,
                'emulatedAreaProcessor' => $this->emulatedAreProcessorMock,
                'localeEmulator' => $this->localeEmulatorMock
            ]
        );
    }

    /**
     * Test get config value
     *
     * @return void
     */
    public function testExecute(): void
    {
        $resolvedConfigPath = 'someScope/someScopeCode/some/config/path';

        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with(self::SCOPE, self::SCOPE_CODE)
            ->willReturn(true);
        $this->pathResolverMock->expects($this->once())
            ->method('resolve')
            ->with(self::CONFIG_PATH, self::SCOPE, self::SCOPE_CODE)
            ->willReturn($resolvedConfigPath);
        $this->configSourceMock->expects($this->once())
            ->method('get')
            ->with($resolvedConfigPath)
            ->willReturn('someValue');
        $this->valueProcessorMock->expects($this->once())
            ->method('process')
            ->with(self::SCOPE, self::SCOPE_CODE, 'someValue', self::CONFIG_PATH)
            ->willReturn('someProcessedValue');
        $this->emulatedAreProcessorMock->expects($this->once())
            ->method('process')
            ->willReturnCallback(function ($function) {
                return $function();
            });
        $this->localeEmulatorMock->expects($this->once())
            ->method('emulate')
            ->willReturnCallback(function ($callback) {
                return $callback();
            });

        $tester = $this->getConfigShowCommandTester(
            self::CONFIG_PATH,
            self::SCOPE,
            self::SCOPE_CODE
        );

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $tester->getStatusCode()
        );
        $this->assertStringContainsString(
            'someProcessedValue',
            $tester->getDisplay()
        );
    }

    /**
     * Test not valid scope or scope code
     *
     * @return void
     */
    public function testNotValidScopeOrScopeCode(): void
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with(self::SCOPE, self::SCOPE_CODE)
            ->willThrowException(new LocalizedException(__('error message')));
        $this->emulatedAreProcessorMock->expects($this->once())
            ->method('process')
            ->willReturnCallback(function ($function) {
                return $function();
            });
        $this->localeEmulatorMock->expects($this->once())
            ->method('emulate')
            ->willReturnCallback(function ($function) {
                return $function();
            });

        $tester = $this->getConfigShowCommandTester(
            self::CONFIG_PATH,
            self::SCOPE,
            self::SCOPE_CODE
        );

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertStringContainsString(
            __('error message')->render(),
            $tester->getDisplay()
        );
    }

    /**
     * Test get config value for not existed path.
     *
     * @return void
     */
    public function testConfigPathNotExist(): void
    {
        $exception = new LocalizedException(
            __('The  "%1" path doesn\'t exist. Verify and try again.', self::CONFIG_PATH)
        );

        $this->pathValidatorMock->expects($this->once())
            ->method('validate')
            ->with(self::CONFIG_PATH)
            ->willThrowException($exception);
        $this->emulatedAreProcessorMock->expects($this->once())
            ->method('process')
            ->willReturnCallback(function ($function) {
                return $function();
            });

        $this->localeEmulatorMock->expects($this->once())
            ->method('emulate')
            ->willReturnCallback(function ($function) {
                return $function();
            });

        $tester = $this->getConfigShowCommandTester(self::CONFIG_PATH);

        $this->assertEquals(
            Cli::RETURN_FAILURE,
            $tester->getStatusCode()
        );
        $this->assertStringContainsString(
            __('The  "%1" path doesn\'t exist. Verify and try again.', self::CONFIG_PATH)->render(),
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

        $tester = new CommandTester($this->model);
        $tester->execute($arguments);

        return $tester;
    }
}
