<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\ConfigOptionsList;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Model\ConfigOptionsList\Session as SessionConfigOptionsList;
use PHPUnit\Framework\TestCase;

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

    public function testGetOptions()
    {
        $options = $this->configList->getOptions();
        $this->assertCount(23, $options);

        $this->assertArrayHasKey(0, $options);
        $this->assertInstanceOf(SelectConfigOption::class, $options[0]);
        $this->assertEquals('session-save', $options[0]->getName());

        $this->assertArrayHasKey(1, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[1]);
        $this->assertEquals('session-save-redis-host', $options[1]->getName());

        $this->assertArrayHasKey(2, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[2]);
        $this->assertEquals('session-save-redis-port', $options[2]->getName());

        $this->assertArrayHasKey(3, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[3]);
        $this->assertEquals('session-save-redis-password', $options[3]->getName());

        $this->assertArrayHasKey(4, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[4]);
        $this->assertEquals('session-save-redis-timeout', $options[4]->getName());

        $this->assertArrayHasKey(5, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[5]);
        $this->assertEquals('session-save-redis-persistent-id', $options[5]->getName());

        $this->assertArrayHasKey(6, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[6]);
        $this->assertEquals('session-save-redis-db', $options[6]->getName());

        $this->assertArrayHasKey(7, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[7]);
        $this->assertEquals('session-save-redis-compression-threshold', $options[7]->getName());

        $this->assertArrayHasKey(8, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[8]);
        $this->assertEquals('session-save-redis-compression-lib', $options[8]->getName());

        $this->assertArrayHasKey(9, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[9]);
        $this->assertEquals('session-save-redis-log-level', $options[9]->getName());

        $this->assertArrayHasKey(10, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[10]);
        $this->assertEquals('session-save-redis-max-concurrency', $options[10]->getName());

        $this->assertArrayHasKey(11, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[11]);
        $this->assertEquals('session-save-redis-break-after-frontend', $options[11]->getName());

        $this->assertArrayHasKey(12, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[12]);
        $this->assertEquals('session-save-redis-break-after-adminhtml', $options[12]->getName());

        $this->assertArrayHasKey(13, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[13]);
        $this->assertEquals('session-save-redis-first-lifetime', $options[13]->getName());

        $this->assertArrayHasKey(14, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[14]);
        $this->assertEquals('session-save-redis-bot-first-lifetime', $options[14]->getName());

        $this->assertArrayHasKey(15, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[15]);
        $this->assertEquals('session-save-redis-bot-lifetime', $options[15]->getName());

        $this->assertArrayHasKey(16, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[16]);
        $this->assertEquals('session-save-redis-disable-locking', $options[16]->getName());

        $this->assertArrayHasKey(17, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[17]);
        $this->assertEquals('session-save-redis-min-lifetime', $options[17]->getName());

        $this->assertArrayHasKey(18, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[18]);
        $this->assertEquals('session-save-redis-max-lifetime', $options[18]->getName());
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

    public function testCreateConfigWithSessionSaveRedis()
    {
        $this->deploymentConfigMock->expects($this->any())->method('get')->willReturn('');

        $expectedConfigData = [
            'session' => [
                'save' => 'redis',
                'redis' => [
                    'host' => '',
                    'port' => '',
                    'password' => '',
                    'timeout' => '',
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

        $options = ['session-save' => 'redis'];

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
     * @dataProvider redisOptionProvider
     */
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
     * @dataProvider invalidOptionsProvider
     */
    public function testValidationWithInvalidOptions($option, $invalidInput, $errorMessage)
    {
        $errors = $this->configList->validate([$option => $invalidInput], $this->deploymentConfigMock);

        $this->assertCount(1, $errors);
        $this->assertSame($errorMessage, $errors[0]);
    }

    /**
     * @return array
     */
    public function redisOptionProvider()
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
    public function invalidOptionsProvider()
    {
        return [
            ['session-save', 'clay-tablet', 'Invalid session handler \'clay-tablet\''],
            ['session-save-redis-log-level', '10', 'Invalid Redis log level \'10\'. Valid range is 0-7, inclusive.'],
            ['session-save-redis-compression-lib', 'foobar', 'Invalid Redis compression library \'foobar\''],
        ];
    }
}
