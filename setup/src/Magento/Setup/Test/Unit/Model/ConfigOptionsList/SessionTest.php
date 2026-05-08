<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\ConfigOptionsList;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Model\ConfigOptionsList\Session as SessionConfigOptionsList;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SessionTest extends TestCase
{
    /**
     * @var \Magento\Setup\Model\ConfigOptionsList\Session
     */
    private $configList;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfigMock;

    protected function setUp(): void
    {
        $this->configList = new SessionConfigOptionsList();

        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
    }

    public function testGetOptions(): void
    {
        $options = $this->configList->getOptions();
        $this->assertCount(47, $options);

        $expectedOptions = [
            [SelectConfigOption::class, 'session-save'],
            [TextConfigOption::class, 'session-save-redis-host'],
            [TextConfigOption::class, 'session-save-redis-port'],
            [TextConfigOption::class, 'session-save-redis-password'],
            [TextConfigOption::class, 'session-save-redis-timeout'],
            [TextConfigOption::class, 'session-save-redis-retries'],
            [TextConfigOption::class, 'session-save-redis-persistent-id'],
            [TextConfigOption::class, 'session-save-redis-db'],
            [TextConfigOption::class, 'session-save-redis-compression-threshold'],
            [TextConfigOption::class, 'session-save-redis-compression-lib'],
            [TextConfigOption::class, 'session-save-redis-log-level'],
            [TextConfigOption::class, 'session-save-redis-max-concurrency'],
            [TextConfigOption::class, 'session-save-redis-break-after-frontend'],
            [TextConfigOption::class, 'session-save-redis-break-after-adminhtml'],
            [TextConfigOption::class, 'session-save-redis-first-lifetime'],
            [TextConfigOption::class, 'session-save-redis-bot-first-lifetime'],
            [TextConfigOption::class, 'session-save-redis-bot-lifetime'],
            [TextConfigOption::class, 'session-save-redis-disable-locking'],
            [TextConfigOption::class, 'session-save-redis-min-lifetime'],
            [TextConfigOption::class, 'session-save-redis-max-lifetime'],
            [TextConfigOption::class, 'session-save-redis-sentinel-master'],
            [TextConfigOption::class, 'session-save-redis-sentinel-servers'],
            [TextConfigOption::class, 'session-save-redis-sentinel-verify-master'],
            [TextConfigOption::class, 'session-save-redis-sentinel-connect-retries'],
            [TextConfigOption::class, 'session-save-valkey-host'],
            [TextConfigOption::class, 'session-save-valkey-port'],
            [TextConfigOption::class, 'session-save-valkey-password'],
            [TextConfigOption::class, 'session-save-valkey-timeout'],
            [TextConfigOption::class, 'session-save-valkey-retries'],
            [TextConfigOption::class, 'session-save-valkey-persistent-id'],
            [TextConfigOption::class, 'session-save-valkey-db'],
            [TextConfigOption::class, 'session-save-valkey-compression-threshold'],
            [TextConfigOption::class, 'session-save-valkey-compression-lib'],
            [TextConfigOption::class, 'session-save-valkey-log-level'],
            [TextConfigOption::class, 'session-save-valkey-max-concurrency'],
            [TextConfigOption::class, 'session-save-valkey-break-after-frontend'],
            [TextConfigOption::class, 'session-save-valkey-break-after-adminhtml'],
            [TextConfigOption::class, 'session-save-valkey-first-lifetime'],
            [TextConfigOption::class, 'session-save-valkey-bot-first-lifetime'],
            [TextConfigOption::class, 'session-save-valkey-bot-lifetime'],
            [TextConfigOption::class, 'session-save-valkey-disable-locking'],
            [TextConfigOption::class, 'session-save-valkey-min-lifetime'],
            [TextConfigOption::class, 'session-save-valkey-max-lifetime'],
            [TextConfigOption::class, 'session-save-valkey-sentinel-master'],
            [TextConfigOption::class, 'session-save-valkey-sentinel-servers'],
            [TextConfigOption::class, 'session-save-valkey-sentinel-verify-master'],
            [TextConfigOption::class, 'session-save-valkey-sentinel-connect-retries'],
        ];

        foreach ($expectedOptions as $index => [$expectedClass, $expectedName]) {
            $this->assertArrayHasKey($index, $options, "Option at index $index not found.");
            $this->assertInstanceOf(
                $expectedClass,
                $options[$index],
                "Option at index $index is not of expected class."
            );
            $this->assertEquals(
                $expectedName,
                $options[$index]->getName(),
                "Option at index $index has incorrect name."
            );
        }
    }

    public function testCreateConfig()
    {
        $configData = $this->configList->createConfig([], $this->deploymentConfigMock);
        $this->assertInstanceOf(ConfigData::class, $configData);
    }

    public function testCreateConfigWithSessionSaveFiles()
    {
        $expectedConfigData = [
            'session' => [
                'save' => 'files'
            ]
        ];

        $options = ['session-save' => 'files'];

        $configData = $this->configList->createConfig($options, $this->deploymentConfigMock);
        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * @param string $backend
     */
    #[DataProvider('sessionBackendProvider')]
    public function testCreateConfigWithSessionSaveBackend(string $backend)
    {
        $this->deploymentConfigMock->expects($this->any())->method('get')->willReturn('');

        $expectedConfigData = [
            'session' => [
                'save' => $backend,
                $backend => [
                    'host' => '',
                    'port' => '',
                    'password' => '',
                    'timeout' => '',
                    'retries' => '',
                    'persistent_identifier' => '',
                    'database' => '',
                    'compression_threshold' => '',
                    'compression_library' => '',
                    'log_level' => '',
                    'max_concurrency' => '',
                    'break_after_frontend' => '',
                    'break_after_adminhtml' => '',
                    'first_lifetime' => '',
                    'bot_first_lifetime' => '',
                    'bot_lifetime' => '',
                    'disable_locking' => '',
                    'min_lifetime' => '',
                    'max_lifetime' => '',
                    'sentinel_master' => '',
                    'sentinel_servers' => '',
                    'sentinel_connect_retries' => '',
                    'sentinel_verify_master' => '',
                ]
            ]
        ];

        $options = ['session-save' => $backend];

        $configData = $this->configList->createConfig($options, $this->deploymentConfigMock);
        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    public function testEmptyCreateConfig()
    {
        $expectedConfigData = [];

        $config = $this->configList->createConfig([], $this->deploymentConfigMock);
        $this->assertEquals($expectedConfigData, $config->getData());
    }

    public function testCreateConfigWithRedisInput()
    {
        $this->deploymentConfigMock->expects($this->any())->method('get')->willReturn('');

        $options = [
            'session-save' => 'redis',
            'session-save-redis-host' => 'localhost',
            'session-save-redis-log-level' => '4',
            'session-save-redis-min-lifetime' => '60',
            'session-save-redis-max-lifetime' => '3600',
        ];

        $expectedConfigData = [
            'session' => [
                'save' => 'redis',
                'redis' => [
                    'host' => 'localhost',
                    'port' => '',
                    'password' => '',
                    'timeout' => '',
                    'retries' => '',
                    'persistent_identifier' => '',
                    'database' => '',
                    'compression_threshold' => '',
                    'compression_library' => '',
                    'log_level' => '4',
                    'max_concurrency' => '',
                    'break_after_frontend' => '',
                    'break_after_adminhtml' => '',
                    'first_lifetime' => '',
                    'bot_first_lifetime' => '',
                    'bot_lifetime' => '',
                    'disable_locking' => '',
                    'min_lifetime' => '60',
                    'max_lifetime' => '3600',
                    'sentinel_master' => '',
                    'sentinel_servers' => '',
                    'sentinel_connect_retries' => '',
                    'sentinel_verify_master' => '',
                ]
            ],

        ];

        $config = $this->configList->createConfig($options, $this->deploymentConfigMock);
        $actualConfigData = $config->getData();

        $this->assertEquals($expectedConfigData, $actualConfigData);
    }

    /**
     * @param string $option
     * @param string $configArrayKey
     * @param string $optionValue
     */
    #[DataProvider('redisOptionProvider')]
    public function testIndividualOptionsAreSetProperly($option, $configArrayKey, $optionValue)
    {
        $configData = $this->configList->createConfig([$option => $optionValue], $this->deploymentConfigMock);
        $redisConfigData = $configData->getData()['session']['redis'];

        $this->assertEquals($redisConfigData[$configArrayKey], $optionValue);
    }

    public function testValidationWithValidOptions()
    {
        $options = [
            'session-save' => 'files',
            'session-save-redis-host' => 'localhost',
            'session-save-redis-compression-library' => 'gzip'
        ];

        $errors = $this->configList->validate($options, $this->deploymentConfigMock);

        $this->assertEmpty($errors);
    }

    /**
     * @param string $option
     * @param string $invalidInput
     * @param string $errorMessage
     */
    #[DataProvider('invalidOptionsProvider')]
    public function testValidationWithInvalidOptions($option, $invalidInput, $errorMessage)
    {
        $errors = $this->configList->validate([$option => $invalidInput], $this->deploymentConfigMock);

        $this->assertCount(1, $errors);
        $this->assertSame($errorMessage, $errors[0]);
    }

    /**
     * @return array
     */
    public static function redisOptionProvider()
    {
        return [
            ['session-save-redis-host', 'host', 'google'],
            ['session-save-redis-port', 'port', '1234'],
            ['session-save-redis-password', 'password', 'secretPassword'],
            ['session-save-redis-timeout', 'timeout', '1000'],
            ['session-save-redis-persistent-id', 'persistent_identifier', 'foo'],
            ['session-save-redis-db', 'database', '5'],
            ['session-save-redis-compression-threshold', 'compression_threshold', '1024'],
            ['session-save-redis-compression-lib', 'compression_library', 'tar'],
            ['session-save-redis-log-level', 'log_level', '2'],
            ['session-save-redis-max-concurrency', 'max_concurrency', '3'],
            ['session-save-redis-break-after-frontend', 'break_after_frontend', '10'],
            ['session-save-redis-break-after-adminhtml', 'break_after_adminhtml', '20'],
            ['session-save-redis-first-lifetime', 'first_lifetime', '300'],
            ['session-save-redis-bot-first-lifetime', 'bot_first_lifetime', '30'],
            ['session-save-redis-bot-lifetime', 'bot_lifetime', '3600'],
            ['session-save-redis-disable-locking', 'disable_locking', '1'],
            ['session-save-redis-min-lifetime', 'min_lifetime', '20'],
            ['session-save-redis-max-lifetime', 'max_lifetime', '12000'],
        ];
    }

    /**
     * @return array
     */
    public static function invalidOptionsProvider()
    {
        return [
            ['session-save', 'clay-tablet', 'Invalid session handler \'clay-tablet\''],
            ['session-save-redis-log-level', '10', 'Invalid Redis log level \'10\'. Valid range is 0-7, inclusive.'],
            ['session-save-redis-compression-lib', 'foobar', 'Invalid Redis compression library \'foobar\''],
        ];
    }

    public static function sessionBackendProvider(): array
    {
        return [
            'Redis backend' => ['redis'],
            'Valkey backend' => ['valkey'],
        ];
    }
}
