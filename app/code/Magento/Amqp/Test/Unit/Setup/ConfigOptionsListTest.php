<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Amqp\Test\Unit\Setup;

use Magento\Amqp\Setup\ConfigOptionsList;
use Magento\Amqp\Setup\ConnectionValidator;
use Magento\MessageQueue\Setup\ConfigOptionsList as MessageQueueConfigOptionsList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConfigOptionsListTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigOptionsList
     */
    private $model;

    /**
     * @var ConnectionValidator|MockObject
     */
    private $connectionValidatorMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConsoleOutput|MockObject
     */
    private $consoleOutputMock;

    /**
     * @var array
     */
    private $options;

    protected function setUp(): void
    {
        $this->options = [
            ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_HOST => 'host',
            ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PORT => 'port',
            ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_USER => 'user',
            ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PASSWORD => 'password',
            ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST => 'virtual host',
            ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL => 'ssl',
            ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS => '{"ssl_option":"test"}',
        ];

        $this->objectManager = new ObjectManager($this);
        $this->connectionValidatorMock = $this->createMock(ConnectionValidator::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->consoleOutputMock = $this->createMock(ConsoleOutput::class);

        $this->model = $this->objectManager->getObject(
            ConfigOptionsList::class,
            [
                'connectionValidator' => $this->connectionValidatorMock,
                'consoleOutput' => $this->consoleOutputMock,
            ]
        );
    }

    public function testGetOptions()
    {
        $expectedOptions = [
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_AMQP_HOST,
                'Amqp server host',
                ConfigOptionsList::DEFAULT_AMQP_HOST
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_AMQP_PORT,
                'Amqp server port',
                ConfigOptionsList::DEFAULT_AMQP_PORT
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_AMQP_USER,
                'Amqp server username',
                ConfigOptionsList::DEFAULT_AMQP_USER
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_AMQP_PASSWORD,
                'Amqp server password',
                ConfigOptionsList::DEFAULT_AMQP_PASSWORD
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST,
                'Amqp virtualhost',
                ConfigOptionsList::DEFAULT_AMQP_VIRTUAL_HOST
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_AMQP_SSL,
                'Amqp SSL',
                ConfigOptionsList::DEFAULT_AMQP_SSL
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS,
                TextConfigOption::FRONTEND_WIZARD_TEXTAREA,
                ConfigOptionsList::CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS,
                'Amqp SSL Options (JSON)',
                ConfigOptionsList::DEFAULT_AMQP_SSL
            ),
        ];
        $this->assertEquals($expectedOptions, $this->model->getOptions());
    }

    /**
     * @param array $options
     * @param array $expectedConfigData
     */
    #[DataProvider('getCreateConfigDataProvider')]
    public function testCreateConfig($options, $expectedConfigData)
    {
        $result = $this->model->createConfig($options, $this->deploymentConfigMock);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        /** @var ConfigData $configData */
        $configData = $result[0];
        $this->assertInstanceOf(ConfigData::class, $configData);
        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    public function testValidateInvalidConnection()
    {
        $expectedResult = ['Could not connect to the Amqp Server.'];
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(false);
        $this->connectionValidatorMock->expects($this->never())->method('getServerVersion');
        $this->assertEquals($expectedResult, $this->model->validate($this->options, $this->deploymentConfigMock));
    }

    public function testValidateValidConnection()
    {
        $expectedResult = [];
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(true);
        $this->connectionValidatorMock->expects($this->once())
            ->method('getServerVersion')
            ->willReturn('4.3.1');
        $this->assertEquals($expectedResult, $this->model->validate($this->options, $this->deploymentConfigMock));
    }

    public function testValidateNoOptions()
    {
        $expectedResult = [];
        $options = [];
        $this->connectionValidatorMock->expects($this->never())->method('isConnectionValid');
        $this->connectionValidatorMock->expects($this->never())->method('getServerVersion');
        $this->assertEquals($expectedResult, $this->model->validate($options, $this->deploymentConfigMock));
    }

    public function testValidateVersionTooLow()
    {
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(true);
        $this->connectionValidatorMock->expects($this->once())
            ->method('getServerVersion')
            ->willReturn('4.2.0');

        $this->consoleOutputMock->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Warning: RabbitMQ version "4.2.0" detected'));

        $errors = $this->model->validate($this->options, $this->deploymentConfigMock);

        $this->assertEmpty($errors);
    }

    public function testValidateVersionExactMinimum()
    {
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(true);
        $this->connectionValidatorMock->expects($this->once())
            ->method('getServerVersion')
            ->willReturn(ConnectionValidator::MINIMUM_RABBITMQ_VERSION);

        $errors = $this->model->validate($this->options, $this->deploymentConfigMock);

        $this->assertEmpty($errors);
    }

    public function testValidateVersionNullSkipsCheck()
    {
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(true);
        $this->connectionValidatorMock->expects($this->once())
            ->method('getServerVersion')
            ->willReturn(null);

        $errors = $this->model->validate($this->options, $this->deploymentConfigMock);

        $this->assertEmpty($errors);
    }

    public function testValidateVersionCheckSkippedWhenDefaultConnectionNotAmqp()
    {
        $options = $this->options;
        $options[MessageQueueConfigOptionsList::INPUT_KEY_QUEUE_DEFAULT_CONNECTION] = 'db';

        $this->connectionValidatorMock->expects($this->never())->method('isConnectionValid');
        $this->connectionValidatorMock->expects($this->never())->method('getServerVersion');

        $errors = $this->model->validate($options, $this->deploymentConfigMock);

        // Errors should be cleared because default connection is not 'amqp'
        $this->assertEmpty($errors);
    }

    /**
     * @return array
     */
    public static function getCreateConfigDataProvider()
    {
        return [
            [
                [
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_HOST => 'host',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PORT => 'port',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_USER => 'user',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PASSWORD => 'password',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST => 'virtual host',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL => 'ssl',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS => '{"ssl_option":"test"}',
                ],
                ['queue' => ['amqp' => [
                    'host' => 'host',
                    'port' => 'port',
                    'user' => 'user',
                    'password' => 'password',
                    'virtualhost' => 'virtual host',
                    'ssl' => 'ssl',
                    'ssl_options' => ['ssl_option' => 'test'],
                ]
                ]
                ],
            ],
            [
                [
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_HOST => 'host',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PORT => ConfigOptionsList::DEFAULT_AMQP_PORT,
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_USER => 'user',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PASSWORD => 'password',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST => 'virtual host',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL => 'ssl',
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS => '{"ssl_option":"test"}',
                ],
                ['queue' => ['amqp' => [
                    'host' => 'host',
                    'port' => ConfigOptionsList::DEFAULT_AMQP_PORT,
                    'user' => 'user',
                    'password' => 'password',
                    'virtualhost' => 'virtual host',
                    'ssl' => 'ssl',
                    'ssl_options' => ['ssl_option' => 'test'],
                ]
                ]
                ],
            ],
            [
                [
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_HOST => ConfigOptionsList::DEFAULT_AMQP_HOST,
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PORT => ConfigOptionsList::DEFAULT_AMQP_PORT,
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_USER => ConfigOptionsList::DEFAULT_AMQP_USER,
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_PASSWORD => ConfigOptionsList::DEFAULT_AMQP_PASSWORD,
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL => ConfigOptionsList::DEFAULT_AMQP_SSL,
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST => ConfigOptionsList::DEFAULT_AMQP_VIRTUAL_HOST
                ],
                [],
            ],
        ];
    }
}
