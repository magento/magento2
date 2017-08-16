<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\State;

class ConfigGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Framework\App\DeploymentConfig | \PHPUnit_Framework_MockObject_MockObject */
    private $deploymentConfigMock;

    /** @var  \Magento\Setup\Model\ConfigGenerator | \PHPUnit_Framework_MockObject_MockObject */
    private $model;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->deploymentConfigMock = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            \Magento\Setup\Model\ConfigGenerator::class,
            ['deploymentConfig' => $this->deploymentConfigMock]
        );
    }

    public function testCreateXFrameConfig()
    {
        $this->deploymentConfigMock->expects($this->atLeastOnce())
            ->method('get')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT)
            ->willReturn(null);
        $configData = $this->model->createXFrameConfig();
        $this->assertSame('SAMEORIGIN', $configData->getData()[ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT]);
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
        $configData = $this->model->createCacheHostsConfig($data);
        $this->assertEquals($expectedData, $configData->getData()[ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS]);
    }

    public function testCreateModeConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(State::PARAM_MODE)
            ->willReturn(null);
        $configData = $this->model->createModeConfig();
        $this->assertSame(State::MODE_DEFAULT, $configData->getData()[State::PARAM_MODE]);
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
}
