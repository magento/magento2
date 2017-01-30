<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Area;
use \Magento\Framework\App\Cron;

class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cron
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stateMock;

    /**
     * @var \Magento\Framework\App\Console\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Console\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    protected function setUp()
    {
        $this->_stateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->_request = $this->getMock('Magento\Framework\App\Console\Request', [], [], '', false);
        $this->_responseMock = $this->getMock('Magento\Framework\App\Console\Response', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->_model = new Cron($this->_stateMock, $this->_request, $this->_responseMock, $this->objectManager);
    }

    public function testLaunchDispatchesCronEvent()
    {
        $configLoader = $this->getMockForAbstractClass('Magento\Framework\ObjectManager\ConfigLoaderInterface');
        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                ['Magento\Framework\ObjectManager\ConfigLoaderInterface', $configLoader],
                ['Magento\Framework\Event\ManagerInterface', $eventManagerMock],
            ]));
        $crontabConfig = ['config'];
        $configLoader->expects($this->once())
            ->method('load')
            ->with(Area::AREA_CRONTAB)
            ->willReturn($crontabConfig);
        $this->objectManager->expects($this->once())
            ->method('configure')
            ->with($crontabConfig);
        $this->_stateMock->expects($this->once())->method('setAreaCode')->with(Area::AREA_CRONTAB);
        $eventManagerMock->expects($this->once())->method('dispatch')->with('default');
        $this->_responseMock->expects($this->once())->method('setCode')->with(0);
        $this->assertEquals($this->_responseMock, $this->_model->launch());
    }
}
