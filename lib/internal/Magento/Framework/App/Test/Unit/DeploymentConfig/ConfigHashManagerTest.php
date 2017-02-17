<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\DeploymentConfig;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\ConfigImporterPool;
use Magento\Framework\App\DeploymentConfig\ConfigHashManager;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Serialize\SerializerInterface;

class ConfigHashManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigHashManager
     */
    private $configHashManager;

    /**
     * @var ConfigImporterPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configImporterPoolMock;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writerMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configImporterPoolMock = $this->getMockBuilder(ConfigImporterPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writerMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->getMockForAbstractClass();

        $this->configHashManager = new ConfigHashManager(
            $this->configImporterPoolMock,
            $this->deploymentConfigMock,
            $this->writerMock,
            $this->serializerMock
        );
    }

    /**
     * @param string|null $savedHash
     * @param bool $expectedValue
     * @return void
     * @dataProvider isHashValidDataProvider
     */
    public function testIsHashValid($sectionName, $savedHash, $expectedValue)
    {
        $this->configImporterPoolMock->expects($this->once())
            ->method('getSections')
            ->willReturn([$sectionName]);
        $this->deploymentConfigMock->expects($this->any())
            ->method('getConfigData')
            ->willReturnMap([
                ['testSection', ['testValue' => '123']],
                ['testSection2', []],
                [ConfigHashManager::CONFIG_KEY, $savedHash],
            ]);
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->with([$sectionName => ['testValue' => '123']])
            ->willReturn('serialized data');

        $this->assertSame($expectedValue, $this->configHashManager->isHashValid());
    }

    /**
     * @return array
     */
    public function isHashValidDataProvider()
    {
        return [
            [
                'sectionName' => 'testSection',
                'savedHash' => '41d9c7f6b3d4a5304d90a7914edf5905bf4ada62',
                'expectedValue' => true
            ],
            [
                'sectionName' => 'testSection',
                'savedHash' => '41d9c7f6b3d4a5304d90a7914edf5905bf4ada63',
                'expectedValue' => false
            ],
            [
                'sectionName' => 'testSection',
                'savedHash' => null,
                'expectedValue' => false
            ],
            [
                'sectionName' => 'testSection2',
                'savedHash' => '41d9c7f6b3d4a5304d90a7914edf5905bf4ada63',
                'expectedValue' => true
            ],
            [
                'sectionName' => 'testSection3',
                'savedHash' => '',
                'expectedValue' => true
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGenerateHash()
    {
        $sectionName = 'testSection';
        $hash = '41d9c7f6b3d4a5304d90a7914edf5905bf4ada62';
        $this->configImporterPoolMock->expects($this->once())
            ->method('getSections')
            ->willReturn([$sectionName]);
        $this->deploymentConfigMock->expects($this->any())
            ->method('getConfigData')
            ->with($sectionName)
            ->willReturn(['testValue' => '123']);
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->with([$sectionName => ['testValue' => '123']])
            ->willReturn('serialized data');
        $this->writerMock->expects($this->once())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_ENV => [ConfigHashManager::CONFIG_KEY => $hash]]);

        $this->configHashManager->generateHash();
    }
}
