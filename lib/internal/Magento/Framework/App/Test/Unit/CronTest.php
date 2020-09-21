<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Console\Request;
use Magento\Framework\App\Console\Response;
use Magento\Framework\App\Cron;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronTest extends TestCase
{
    /**
     * @var Cron
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_configScopeMock;

    /**
     * @var State|MockObject
     */
    protected $_stateMock;

    /**
     * @var Request|MockObject
     */
    protected $_request;

    /**
     * @var Response|MockObject
     */
    protected $_responseMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->_stateMock = $this->createMock(State::class);
        $this->_request = $this->createMock(Request::class);
        $this->_responseMock = $this->createMock(Response::class);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
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
     * @return MockObject
     */
    protected function prepareAreaListMock()
    {
        $areaMock = $this->createMock(Area::class);
        $areaMock->expects($this->once())
            ->method('load')
            ->with(Area::PART_TRANSLATE);

        $areaListMock = $this->createMock(AreaList::class);
        $areaListMock->expects($this->any())
            ->method('getArea')
            ->with(Area::AREA_CRONTAB)
            ->willReturn($areaMock);

        return $areaListMock;
    }

    public function testLaunchDispatchesCronEvent()
    {
        $configLoader = $this->getMockForAbstractClass(ConfigLoaderInterface::class);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->objectManager->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [ConfigLoaderInterface::class, $configLoader],
                [ManagerInterface::class, $eventManagerMock]
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
