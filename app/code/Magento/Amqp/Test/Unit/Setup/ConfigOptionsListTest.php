<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Test\Unit\Setup;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Amqp\Setup\ConfigOptionsList;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\App\DeploymentConfig;

class ConfigOptionsListTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Amqp\Setup\ConnectionValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionValidatorMock;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $deploymentConfigMock;

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
        $this->connectionValidatorMock = $this->getMockBuilder(\Magento\Amqp\Setup\ConnectionValidator::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->deploymentConfigMock = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Amqp\Setup\ConfigOptionsList::class,
            [
                'connectionValidator' => $this->connectionValidatorMock,
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
     * @dataProvider getCreateConfigDataProvider
     */
    public function testCreateConfig($options, $expectedConfigData)
    {
        $result = $this->model->createConfig($options, $this->deploymentConfigMock);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        /** @var \Magento\Framework\Config\Data\ConfigData $configData */
        $configData = $result[0];
        $this->assertInstanceOf(\Magento\Framework\Config\Data\ConfigData::class, $configData);
        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    public function testValidateInvalidConnection()
    {
        $expectedResult = ['Could not connect to the Amqp Server.'];
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(false);
        $this->assertEquals($expectedResult, $this->model->validate($this->options, $this->deploymentConfigMock));
    }

    public function testValidateValidConnection()
    {
        $expectedResult = [];
        $this->connectionValidatorMock->expects($this->once())->method('isConnectionValid')->willReturn(true);
        $this->assertEquals($expectedResult, $this->model->validate($this->options, $this->deploymentConfigMock));
    }

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
    public function getCreateConfigDataProvider()
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
                ['queue' =>
                    ['amqp' =>
                        [
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
                ['queue' =>
                    ['amqp' =>
                        [
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
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST =>
                        ConfigOptionsList::DEFAULT_AMQP_VIRTUAL_HOST,
                    ConfigOptionsList::INPUT_KEY_QUEUE_AMQP_SSL => ConfigOptionsList::DEFAULT_AMQP_SSL,
                ],
                [],
            ],
        ];
    }
}
