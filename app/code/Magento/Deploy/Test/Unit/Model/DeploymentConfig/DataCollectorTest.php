<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Framework\App\DeploymentConfig;

class DataCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImporterPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configImporterPoolMock;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    /**
     * @return void
     */
    protected function setUp()
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
        $this->deploymentConfigMock->expects($this->any())
            ->method('getConfigData')
            ->willReturnMap([['first', 'some data']]);

        $this->assertSame(['first' => 'some data'], $this->dataCollector->getConfig());
    }
}
