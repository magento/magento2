<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\Hash\Generator;
use Magento\Deploy\Model\DeploymentConfig\DataCollector;

class HashTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var Writer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writerMock;

    /**
     * @var Generator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHashGeneratorMock;

    /**
     * @var DataCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataConfigCollectorMock;

    /**
     * @var Hash
     */
    private $hash;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writerMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHashGeneratorMock = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConfigCollectorMock = $this->getMockBuilder(DataCollector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->hash = new Hash(
            $this->deploymentConfigMock,
            $this->writerMock,
            $this->configHashGeneratorMock,
            $this->dataConfigCollectorMock
        );
    }

    /**
     * @param string|array|null $dataFromStorage
     * @param array $expectedResult
     * @return void
     * @dataProvider getDataProvider
     */
    public function testGet($dataFromStorage, $expectedResult)
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Hash::CONFIG_KEY)
            ->willReturn($dataFromStorage);

        $this->assertSame($expectedResult, $this->hash->get());
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [['section' => 'hash'], ['section' => 'hash']],
            ['hash', ['hash']],
            ['', []],
            [null, []],
        ];
    }

    /**
     * @return void
     */
    public function testRegenerate()
    {
        $section = 'section';
        $config = 'some config';
        $fullConfig = ['section' => $config];
        $hash = 'some hash';
        $hashes = [$section => $hash];

        $this->generalRegenerateMocks($fullConfig, $config, $hash);
        $this->writerMock->expects($this->once())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_ENV => [Hash::CONFIG_KEY => $hashes]]);

        $this->hash->regenerate();
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Hash has not been saved
     */
    public function testRegenerateWithException()
    {
        $section = 'section';
        $config = 'some config';
        $fullConfig = ['section' => $config];
        $hash = 'some hash';
        $hashes = [$section => $hash];

        $this->generalRegenerateMocks($fullConfig, $config, $hash, $section);
        $this->writerMock->expects($this->once())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_ENV => [Hash::CONFIG_KEY => $hashes]])
            ->willThrowException(new FileSystemException(__('Some error')));

        $this->hash->regenerate($section);
    }

    /**
     * @param array $fullConfig
     * @param string $config
     * @param string $hash
     * @param string|null $sectionName
     * @return void
     */
    private function generalRegenerateMocks($fullConfig, $config, $hash, $sectionName = null)
    {
        $this->dataConfigCollectorMock->expects($this->once())
            ->method('getConfig')
            ->with($sectionName)
            ->willReturn($fullConfig);
        $this->configHashGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($config)
            ->willReturn($hash);
    }
}
