<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager as AppObjectManager;

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

        $this->valueProcessorMock = $this->createMock(ValueProcessor::class);
        $this->pathResolverMock = $this->createMock(ConfigPathResolver::class);
        $this->scopeValidatorMock = $this->createMock(ValidatorInterface::class);
        $this->configSourceMock = $this->createMock(ConfigSourceInterface::class);
        $this->pathValidatorMock = $this->createMock(PathValidator::class);
        $pathValidatorFactoryMock = $this->createMock(PathValidatorFactory::class);
        $pathValidatorFactoryMock->expects($this->atMost(1))
            ->method('create')
            ->willReturn($this->pathValidatorMock);

        $this->emulatedAreProcessorMock = $this->createMock(EmulatedAdminhtmlAreaProcessor::class);

        $this->localeEmulatorMock = $this->createMock(LocaleEmulatorInterface::class);

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

    /**
     * Test that design configuration paths are validated correctly using Theme PathValidator
     *
     * @return void
     */
    public function testExecuteWithDesignPath(): void
    {
        $designPath = 'design/theme/theme_id';
        $resolvedConfigPath = 'default//design/theme/theme_id';

        // Create a separate test setup with Theme PathValidator mock
        $objectManager = new ObjectManager($this);

        $valueProcessorMock = $this->createMock(ValueProcessor::class);
        $pathResolverMock = $this->createMock(ConfigPathResolver::class);
        $scopeValidatorMock = $this->createMock(ValidatorInterface::class);
        $configSourceMock = $this->createMock(ConfigSourceInterface::class);
        // Use Theme PathValidator mock instead of base PathValidator
        $themePathValidatorMock = $this->createMock(\Magento\Theme\Model\Config\PathValidator::class);
        $pathValidatorFactoryMock = $this->createMock(PathValidatorFactory::class);
        $pathValidatorFactoryMock->expects($this->atMost(1))
            ->method('create')
            ->willReturn($themePathValidatorMock);

        $emulatedAreaProcessorMock = $this->createMock(EmulatedAdminhtmlAreaProcessor::class);
        $localeEmulatorMock = $this->createMock(LocaleEmulatorInterface::class);

        $model = $objectManager->getObject(
            ConfigShowCommand::class,
            [
                'scopeValidator' => $scopeValidatorMock,
                'configSource' => $configSourceMock,
                'pathResolver' => $pathResolverMock,
                'valueProcessor' => $valueProcessorMock,
                'pathValidatorFactory' => $pathValidatorFactoryMock,
                'emulatedAreaProcessor' => $emulatedAreaProcessorMock,
                'localeEmulator' => $localeEmulatorMock
            ]
        );

        $scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, '')
            ->willReturn(true);

        $themePathValidatorMock->expects($this->once())
            ->method('validate')
            ->with($designPath)
            ->willReturn(true);

        $pathResolverMock->expects($this->once())
            ->method('resolve')
            ->with($designPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, '')
            ->willReturn($resolvedConfigPath);

        $configSourceMock->expects($this->once())
            ->method('get')
            ->with($resolvedConfigPath)
            ->willReturn('3');

        $valueProcessorMock->expects($this->once())
            ->method('process')
            ->with(ScopeConfigInterface::SCOPE_TYPE_DEFAULT, '', '3', $designPath)
            ->willReturn('3');

        $emulatedAreaProcessorMock->expects($this->once())
            ->method('process')
            ->willReturnCallback(function ($function) {
                return $function();
            });

        $localeEmulatorMock->expects($this->once())
            ->method('emulate')
            ->willReturnCallback(function ($callback) {
                return $callback();
            });

        $tester = new CommandTester($model);
        $tester->execute([
            ConfigShowCommand::INPUT_ARGUMENT_PATH => $designPath
        ]);

        $this->assertEquals(
            Cli::RETURN_SUCCESS,
            $tester->getStatusCode()
        );
        $this->assertStringContainsString(
            '3',
            $tester->getDisplay()
        );
    }

    /**
     * Test that array config values are processed recursively
     *
     * @return void
     */
    public function testExecuteWithArrayConfigValue(): void
    {
        $resolvedConfigPath = 'someScope/someScopeCode/some/config/path';
        $arrayConfigValue = [
            'child1' => 'value1',
            'child2' => [
                'grandchild' => 'value2'
            ]
        ];

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
            ->willReturn($arrayConfigValue);

        // Mock valueProcessor for each array element using callback
        $callCount = 0;
        $this->valueProcessorMock->expects($this->exactly(2))
            ->method('process')
            ->willReturnCallback(function ($scope, $scopeCode, $value, $path) use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    $this->assertEquals(self::SCOPE, $scope);
                    $this->assertEquals(self::SCOPE_CODE, $scopeCode);
                    $this->assertEquals('value1', $value);
                    $this->assertEquals(self::CONFIG_PATH . '/child1', $path);
                    return 'processedValue1';
                } elseif ($callCount === 2) {
                    $this->assertEquals(self::SCOPE, $scope);
                    $this->assertEquals(self::SCOPE_CODE, $scopeCode);
                    $this->assertEquals('value2', $value);
                    $this->assertEquals(self::CONFIG_PATH . '/child2/grandchild', $path);
                    return 'processedValue2';
                }
                return '';
            });

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

        $display = $tester->getDisplay();
        $this->assertStringContainsString(
            self::CONFIG_PATH . '/child1 - processedValue1',
            $display
        );
        $this->assertStringContainsString(
            self::CONFIG_PATH . '/child2/grandchild - processedValue2',
            $display
        );
    }

    /**
     * Test fallback config value retrieval with case-insensitive scope code and path
     *
     * @return void
     */
    public function testExecuteWithFallbackConfigRetrieval(): void
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with(self::SCOPE, self::SCOPE_CODE)
            ->willReturn(true);

        $resolveCallCount = 0;
        $this->pathResolverMock->expects($this->exactly(2))
            ->method('resolve')
            ->willReturnCallback(function ($path, $scope, $scopeCode) use (&$resolveCallCount) {
                $resolveCallCount++;
                if ($resolveCallCount === 1) {
                    $this->assertEquals(self::CONFIG_PATH, $path);
                    $this->assertEquals(self::SCOPE, $scope);
                    $this->assertEquals(self::SCOPE_CODE, $scopeCode);
                    return 'someScope/someScopeCode/some/config/path';
                } else {
                    $this->assertEquals(self::CONFIG_PATH, $path);
                    $this->assertEquals(self::SCOPE, $scope);
                    $this->assertEquals(strtolower(self::SCOPE_CODE), $scopeCode);
                    return 'someScope/somescopecode/some/config/path';
                }
            });

        // Mock configSource with callback to handle consecutive calls
        $getCallCount = 0;
        $this->configSourceMock->expects($this->exactly(3))
            ->method('get')
            ->willReturnCallback(function ($path) use (&$getCallCount) {
                $getCallCount++;
                if ($getCallCount === 1) {
                    $this->assertEquals('someScope/someScopeCode/some/config/path', $path);
                    return null; // First attempt fails
                } elseif ($getCallCount === 2) {
                    $this->assertEquals('someScope/somescopecode/some/config/path', $path);
                    return null; // Second attempt fails
                } else {
                    $this->assertEquals('somescope/somescopecode/some/config/path', $path);
                    return 'fallbackValue'; // Third attempt succeeds
                }
            });

        $this->valueProcessorMock->expects($this->once())
            ->method('process')
            ->with(self::SCOPE, self::SCOPE_CODE, 'fallbackValue', self::CONFIG_PATH)
            ->willReturn('processedFallbackValue');

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
            'processedFallbackValue',
            $tester->getDisplay()
        );
    }

    /**
     * Test constructor ObjectManager fallback when optional dependencies are null
     *
     * @return void
     */
    public function testConstructorObjectManagerFallback(): void
    {
        $scopeValidatorMock = $this->createMock(ValidatorInterface::class);
        $configSourceMock = $this->createMock(ConfigSourceInterface::class);
        $pathResolverMock = $this->createMock(ConfigPathResolver::class);
        $valueProcessorMock = $this->createMock(ValueProcessor::class);
        $pathValidatorFactoryMock = $this->createMock(PathValidatorFactory::class);
        $emulatedAreaProcessorMock = $this->createMock(EmulatedAdminhtmlAreaProcessor::class);
        $originalOm = null;
        $hadOriginal = true;
        try {
            $originalOm = \Magento\Framework\App\ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            $hadOriginal = false;
        }
        $objectManagerMock = $this->getMockForAbstractClass(
            ObjectManagerInterface::class
        );
        $objectManagerMock->method('get')
            ->willReturnCallback(function ($type) use (
                $pathValidatorFactoryMock,
                $emulatedAreaProcessorMock
            ) {
                if ($type === PathValidatorFactory::class) {
                    return $pathValidatorFactoryMock;
                }
                if ($type === EmulatedAdminhtmlAreaProcessor::class) {
                    return $emulatedAreaProcessorMock;
                }
                return null;
            });
        AppObjectManager::setInstance($objectManagerMock);

        try {
            $command = new ConfigShowCommand(
                $scopeValidatorMock,
                $configSourceMock,
                $pathResolverMock,
                $valueProcessorMock,
                null,
                null,
                null
            );
            $this->assertSame($scopeValidatorMock, $this->getPrivateProperty($command, 'scopeValidator'));
            $this->assertSame($configSourceMock, $this->getPrivateProperty($command, 'configSource'));
            $this->assertSame($pathResolverMock, $this->getPrivateProperty($command, 'pathResolver'));
            $this->assertSame($valueProcessorMock, $this->getPrivateProperty($command, 'valueProcessor'));
            $this->assertSame($pathValidatorFactoryMock, $this->getPrivateProperty($command, 'pathValidatorFactory'));
            $this->assertSame($emulatedAreaProcessorMock, $this->getPrivateProperty($command, 'emulatedAreaProcessor'));
            $this->assertNull($this->getPrivateProperty($command, 'localeEmulator'));

        } finally {
            if ($hadOriginal && $originalOm instanceof ObjectManagerInterface) {
                AppObjectManager::setInstance($originalOm);
            } else {
                AppObjectManager::setInstance($objectManagerMock);
            }
        }
    }

    /**
     * Helper method to access private properties for testing
     *
     * @param object $object
     * @param string $property
     * @return mixed
     */
    private function getPrivateProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        return $prop->getValue($object);
    }
}
