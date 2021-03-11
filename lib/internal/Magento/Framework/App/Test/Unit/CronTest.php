<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Area;
use \Magento\Framework\App\Cron;

class CronTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Cron
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_stateMock;

    /**
     * @var \Magento\Framework\App\Console\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Console\Response|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_responseMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->_stateMock = $this->createMock(\Magento\Framework\App\State::class);
        $this->_request = $this->createMock(\Magento\Framework\App\Console\Request::class);
        $this->_responseMock = $this->createMock(\Magento\Framework\App\Console\Response::class);
        $this->objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new Cron(
            $this->_stateMock,
            $this->_request,
            $this->_responseMock,
            $this->objectManager,
            [],
            $this->prepareAreaListMock()
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function prepareAreaListMock()
    {
        $areaMock = $this->createMock(\Magento\Framework\App\Area::class);
        $areaMock->expects($this->once())
            ->method('load')
            ->with(Area::PART_TRANSLATE);

        $areaListMock = $this->createMock(\Magento\Framework\App\AreaList::class);
        $areaListMock->expects($this->any())
            ->method('getArea')
            ->with(Area::AREA_CRONTAB)
            ->willReturn($areaMock);

        return $areaListMock;
    }

    public function testLaunchDispatchesCronEvent()
    {
        $configLoader = $this->getMockForAbstractClass(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class);
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [\Magento\Framework\ObjectManager\ConfigLoaderInterface::class, $configLoader],
                [\Magento\Framework\Event\ManagerInterface::class, $eventManagerMock]
            ]);
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
