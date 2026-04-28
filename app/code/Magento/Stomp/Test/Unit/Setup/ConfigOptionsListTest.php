<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Stomp\Test\Unit\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Stomp\Setup\ConfigOptionsList;
use Magento\Stomp\Setup\ConnectionValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigOptionsListTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var ConfigOptionsList
     */
    private $model;

    /**
     * @var ConnectionValidator|MockObject
     */
    private ConnectionValidator|MockObject $connectionValidatorMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private DeploymentConfig|MockObject $deploymentConfigMock;

    /**
     * @var array
     */
    private array $options;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->options = [
            ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_HOST => 'host',
            ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PORT => 'port',
            ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_USER => 'user',
            ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PASSWORD => 'password',
            ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL => 'ssl',
            ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS => '{"ssl_option":"test"}',
        ];

        $this->objectManager = new ObjectManager($this);
        $this->connectionValidatorMock = $this->createMock(ConnectionValidator::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);

        $this->model = $this->objectManager->getObject(
            ConfigOptionsList::class,
            [
                'connectionValidator' => $this->connectionValidatorMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetOptions()
    {
        $expectedOptions = [
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_STOMP_HOST,
                'Stomp server host',
                ConfigOptionsList::DEFAULT_STOMP_HOST
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_STOMP_PORT,
                'Stomp server port',
                ConfigOptionsList::DEFAULT_STOMP_PORT
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_STOMP_USER,
                'Stomp server username',
                ConfigOptionsList::DEFAULT_STOMP_USER
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_STOMP_PASSWORD,
                'Stomp server password',
                ConfigOptionsList::DEFAULT_STOMP_PASSWORD
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_STOMP_SSL,
                'Stomp SSL',
                ConfigOptionsList::DEFAULT_STOMP_SSL
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS,
                TextConfigOption::FRONTEND_WIZARD_TEXTAREA,
                ConfigOptionsList::CONFIG_PATH_QUEUE_STOMP_SSL_OPTIONS,
                'Stomp SSL Options (JSON)',
                ConfigOptionsList::DEFAULT_STOMP_SSL
            )
        ];
        $this->assertEquals($expectedOptions, $this->model->getOptions());
    }

    /**
     * @param array $options
     * @param array $expectedConfigData
     */
    #[DataProvider('getCreateConfigDataProvider')]
    public function testCreateConfig(array $options, array $expectedConfigData)
    {
        $result = $this->model->createConfig($options, $this->deploymentConfigMock);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        /** @var ConfigData $configData */
        $configData = $result[0];
        $this->assertInstanceOf(ConfigData::class, $configData);
        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * @return void
     */
    public function testValidateInvalidConnection()
    {
        $expectedResult = ['Could not connect to the Stomp Server.'];
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(false);
        $this->assertEquals($expectedResult, $this->model->validate($this->options, $this->deploymentConfigMock));
    }

    /**
     * @return void
     */
    public function testValidateValidConnection()
    {
        $expectedResult = [];
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(true);
        $this->assertEquals($expectedResult, $this->model->validate($this->options, $this->deploymentConfigMock));
    }

    /**
     * @return void
     */
    public function testValidateNoOptions()
    {
        $expectedResult = [];
        $options = [];
        $this->connectionValidatorMock->expects($this->never())->method('isConnectionValid');
        $this->assertEquals($expectedResult, $this->model->validate($options, $this->deploymentConfigMock));
    }

    /**
     * @return array
     */
    public static function getCreateConfigDataProvider(): array
    {
        return [
            [
                [
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_HOST => 'host',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PORT => 'port',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_USER => 'user',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PASSWORD => 'password',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL => 'ssl',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS => '{"ssl_option":"test"}',
                ],
                ['queue' => ['stomp' => [
                    'host' => 'host',
                    'port' => 'port',
                    'user' => 'user',
                    'password' => 'password',
                    'ssl' => 'ssl',
                    'ssl_options' => ['ssl_option' => 'test'],
                ]
                ]
                ],
            ],
            [
                [
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_HOST => 'host',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PORT => ConfigOptionsList::DEFAULT_STOMP_PORT,
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_USER => 'user',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PASSWORD => 'password',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL => 'ssl',
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS => '{"ssl_option":"test"}',
                ],
                ['queue' => ['stomp' => [
                    'host' => 'host',
                    'port' => ConfigOptionsList::DEFAULT_STOMP_PORT,
                    'user' => 'user',
                    'password' => 'password',
                    'ssl' => 'ssl',
                    'ssl_options' => ['ssl_option' => 'test'],
                ]
                ]
                ],
            ],
            [
                [
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_HOST => ConfigOptionsList::DEFAULT_STOMP_HOST,
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PORT => ConfigOptionsList::DEFAULT_STOMP_PORT,
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_USER => ConfigOptionsList::DEFAULT_STOMP_USER,
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_PASSWORD => ConfigOptionsList::DEFAULT_STOMP_PASSWORD,
                    ConfigOptionsList::INPUT_KEY_QUEUE_STOMP_SSL => ConfigOptionsList::DEFAULT_STOMP_SSL
                ],
                [],
            ],
        ];
    }
}
