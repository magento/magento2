<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataCollectorTest extends TestCase
{
    /**
     * @var ImporterPool|MockObject
     */
    private $configImporterPoolMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configImporterPoolMock = $this->getMockBuilder(ImporterPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataCollector = new DataCollector($this->configImporterPoolMock, $this->deploymentConfigMock);
    }

    /**
     * @return void
     */
    public function testGetConfig()
    {
        $sections = ['first', 'second'];
        $this->configImporterPoolMock->expects($this->once())
            ->method('getSections')
            ->willReturn($sections);
        $this->deploymentConfigMock->expects($this->atLeastOnce())
            ->method('getConfigData')
            ->willReturnMap([['first', 'some data']]);

        $this->assertSame(['first' => 'some data', 'second' => null], $this->dataCollector->getConfig());
    }

    /**
     * @return void
     */
    public function testGetConfigSpecificSection()
    {
        $this->configImporterPoolMock->expects($this->never())
            ->method('getSections');
        $this->deploymentConfigMock->expects($this->atLeastOnce())
            ->method('getConfigData')
            ->willReturnMap([['someSection', 'some data']]);
        $this->assertSame(['someSection' => 'some data'], $this->dataCollector->getConfig('someSection'));
    }
}
