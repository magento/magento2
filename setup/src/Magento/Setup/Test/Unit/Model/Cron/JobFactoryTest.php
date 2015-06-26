<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobFactory;

class JobFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobFactory
     */
    private $jobFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager;

    public function setUp()
    {
        $this->serviceManager = $this->getMockForAbstractClass(
            'Zend\ServiceManager\ServiceLocatorInterface',
            [],
            '',
            false
        );

        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $status->expects($this->once())->method('getStatusFilePath')->willReturn('path_a');
        $status->expects($this->once())->method('getLogFilePath')->willReturn('path_b');
        $command = $this->getMock('Magento\Setup\Console\Command\UpgradeCommand', [], [], '', false);
        $maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface', [], '', false);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($objectManager);

        $returnValueMap =[
            ['Magento\Setup\Model\Cron\Status', $status],
            ['Magento\Setup\Console\Command\UpgradeCommand', $command],
            ['Magento\Framework\App\MaintenanceMode', $maintenanceMode],
            ['Magento\Setup\Model\ObjectManagerProvider', $objectManagerProvider]
        ];

        $this->serviceManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));

        $this->jobFactory = new JobFactory($this->serviceManager);
    }

    public function testCreate()
    {


        $this->assertInstanceOf('Magento\Setup\Model\Cron\AbstractJob', $this->jobFactory->create('setup:upgrade', []));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage job is not supported
     */
    public function testCreateUnknownJob()
    {
        $this->jobFactory->create('unknown', []);
    }
}

// functions to override native php functions
namespace Magento\Setup\Model\Cron;

function fopen()
{
    return 'filestream';
}

function is_resource()
{
    return true;
}

function get_resource_type()
{
    return 'stream';
}
