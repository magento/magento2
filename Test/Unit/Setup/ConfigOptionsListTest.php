<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Test\Unit\Setup;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Amqp\Setup\ConfigOptionsList;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\App\DeploymentConfig;

class ConfigOptionsListTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Amqp\Setup\ConnectionValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionValidatorMock;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var array
     */
    private $options;

    protected function setUp()
    {
        $this->options = [
            ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_HOST => 'host',
            ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_PORT => 'port',
            ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_USER => 'user',
            ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD => 'password',
            ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST => 'virtual host',
            ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_SSL => 'ssl',

        ];

        $this->objectManager = new ObjectManager($this);
        $this->connectionValidatorMock = $this->getMockBuilder('Magento\Amqp\Setup\ConnectionValidator')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->deploymentConfigMock = $this->getMockBuilder('Magento\Framework\App\DeploymentConfig')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model = $this->objectManager->getObject(
            'Magento\Amqp\Setup\ConfigOptionsList',
            [
                'connectionValidator' => $this->connectionValidatorMock,
            ]
        );
    }

    public function testGetOptions()
    {
        $expectedOptions = [
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_RABBITMQ_HOST,
                'RabbitMQ server host',
                ConfigOptionsList::DEFAULT_RABBITMQ_HOST
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_RABBITMQ_PORT,
                'RabbitMQ server port',
                ConfigOptionsList::DEFAULT_RABBITMQ_PORT
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_RABBITMQ_USER,
                'RabbitMQ server username',
                ConfigOptionsList::DEFAULT_RABBITMQ_USER
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_RABBITMQ_PASSWORD,
                'RabbitMQ server password',
                ConfigOptionsList::DEFAULT_RABBITMQ_PASSWORD
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_RABBITMQ_VIRTUAL_HOST,
                'RabbitMQ virtualhost',
                ConfigOptionsList::DEFAULT_RABBITMQ_VIRTUAL_HOST
            ),
            new TextConfigOption(
                ConfigOptionsList::INPUT_KEY_QUEUE_RABBITMQ_SSL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsList::CONFIG_PATH_QUEUE_RABBITMQ_SSL,
                'RabbitMQ SSL',
                ConfigOptionsList::DEFAULT_RABBITMQ_SSL
            )
        ];
        $this->assertEquals($expectedOptions, $this->model->getOptions());
    }

    public function testCreateConfig()
    {
        $expectedConfigData = ['queue' =>
            ['rabbit' =>
                [
                    'host' => 'host',
                    'port' => 'port',
                    'user' => 'user',
                    'password' => 'password',
                    'virtualhost' => 'virtual host',
                    'ssl' => 'ssl',
                 ]
            ]
        ];

        $result = $this->model->createConfig($this->options, $this->deploymentConfigMock);
        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        /** @var \Magento\Framework\Config\Data\ConfigData $configData */
        $configData = $result[0];
        $this->assertInstanceOf('Magento\Framework\Config\Data\ConfigData', $configData);
        $actualData = $configData->getData();
        $this->assertEquals($expectedConfigData, $actualData);
    }

    public function testValidateInvalidConnection()
    {
        $expectedResult = ['Could not connect to the RabbitMq Server.'];
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
}
