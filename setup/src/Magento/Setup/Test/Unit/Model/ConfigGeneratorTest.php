<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\State;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\Data\ConfigDataFactory;
use Magento\Setup\Model\ConfigGenerator;

class ConfigGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeploymentConfig | \PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConfigGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var ConfigData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configDataMock;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configDataMock = $this->getMockBuilder(ConfigData::class)
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();

        $configDataFactoryMock = $this->getMockBuilder(ConfigDataFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $configDataFactoryMock->method('create')
            ->willReturn($this->configDataMock);

        $this->model = $objectManager->getObject(
            ConfigGenerator::class,
            [
                'deploymentConfig'  => $this->deploymentConfigMock,
                'configDataFactory' => $configDataFactoryMock,
            ]
        );
    }

    public function testCreateXFrameConfig()
    {
        $this->deploymentConfigMock->expects($this->atLeastOnce())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT)
            ->willReturn(null);

        $this->configDataMock
            ->expects($this->once())
            ->method('set')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT, 'SAMEORIGIN');

        $this->model->createXFrameConfig();
    }

    public function testCreateCacheHostsConfig()
    {
        $data = [ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS => 'localhost:8080, website.com, 120.0.0.1:90'];
        $expectedData = [
            0 => [
                'host' => 'localhost',
                'port' => '8080',
            ],
            1 => [
                'host' => 'website.com',
            ],
            2 => [
                'host' => '120.0.0.1',
                'port' => '90',
            ],
        ];

        $this->configDataMock
            ->expects($this->once())
            ->method('set')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS, $expectedData);

        $this->model->createCacheHostsConfig($data);
    }

    public function testCreateModeConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(State::PARAM_MODE)
            ->willReturn(null);

        $this->configDataMock
            ->expects($this->once())
            ->method('set')
            ->with(State::PARAM_MODE, State::MODE_DEFAULT);

        $this->model->createModeConfig();
    }

    public function testCreateModeConfigIfAlreadySet()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(State::PARAM_MODE)
            ->willReturn(State::MODE_PRODUCTION);
        $configData = $this->model->createModeConfig();
        $this->assertSame([], $configData->getData());
    }

    public function testCreateCryptKeyConfig()
    {
        $key = 'my-new-key';
        $data = [ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY => $key];

        $this->deploymentConfigMock
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY)
            ->willReturn(null);

        $this->configDataMock
            ->expects($this->once())
            ->method('set')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY, $key);

        $this->model->createCryptConfig($data);
    }
}
