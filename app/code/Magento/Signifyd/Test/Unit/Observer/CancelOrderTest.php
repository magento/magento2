<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Signifyd\Api\GuaranteeCancelingServiceInterface;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\Guarantee\CancelGuaranteeAbility;
use Magento\Signifyd\Observer\CancelOrder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CancelOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CancelOrder
     */
    private $observer;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var CancelGuaranteeAbility|MockObject
     */
    private $guaranteeAbility;

    /**
     * @var GuaranteeCancelingServiceInterface|MockObject
     */
    private $cancelingService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isActive'])
            ->getMock();

        $this->guaranteeAbility = $this->getMockBuilder(CancelGuaranteeAbility::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAvailable'])
            ->getMock();

        $this->cancelingService = $this->getMockBuilder(GuaranteeCancelingServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = $objectManager->getObject(CancelOrder::class, [
            'config' => $this->config,
            'guaranteeAbility' => $this->guaranteeAbility,
            'cancelingService' => $this->cancelingService
        ]);
    }

    /**
     * Checks a test case, when Signifyd does not enabled.
     *
     * @covers \Magento\Signifyd\Observer\CancelOrder::execute
     */
    public function testExecuteWithDisabledConfiguration()
    {
        $order = null;
        $this->config->expects(self::once())
            ->method('isActive')
            ->willReturn(false);

        $this->guaranteeAbility->expects(self::never())
            ->method('isAvailable');

        $this->cancelingService->expects(self::never())
            ->method('cancelForOrder');

        /** @var Observer|MockObject $observer */
        $observer = $this->getObserverMock($order);
        $this->observer->execute($observer);
    }

    /**
     * Checks a test case, when not order entity in observer event.
     *
     * @covers \Magento\Signifyd\Observer\CancelOrder::execute
     */
    public function testExecuteWithNonExistsOrder()
    {
        $order = null;
        $this->config->expects(self::once())
            ->method('isActive')
            ->willReturn(true);

        $this->guaranteeAbility->expects(self::never())
            ->method('isAvailable');

        $this->cancelingService->expects(self::never())
            ->method('cancelForOrder');

        /** @var Observer|MockObject $observer */
        $observer = $this->getObserverMock($order);
        $this->observer->execute($observer);
    }

    /**
     * Checks a case, when case guarantee is not available for cancelling.
     *
     * @covers \Magento\Signifyd\Observer\CancelOrder::execute
     */
    public function testExecuteWithNonEligibleGuarantee()
    {
        $entityId = 1;
        /** @var OrderInterface|MockObject $order */
        $order = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects(self::once())
            ->method('getEntityId')
            ->willReturn($entityId);

        $this->config->expects(self::once())
            ->method('isActive')
            ->willReturn(true);

        $this->guaranteeAbility->expects(self::once())
            ->method('isAvailable')
            ->with(self::equalTo($entityId))
            ->willReturn(false);

        $this->cancelingService->expects(self::never())
            ->method('cancelForOrder');

        /** @var Observer|MockObject $observer */
        $observer = $this->getObserverMock($order);
        $this->observer->execute($observer);
    }

    /**
     * Checks a case, when case guarantee submitted for cancelling.
     *
     * @covers \Magento\Signifyd\Observer\CancelOrder::execute
     */
    public function testExecute()
    {
        $entityId = 1;
        /** @var OrderInterface|MockObject $order */
        $order = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects(self::exactly(2))
            ->method('getEntityId')
            ->willReturn($entityId);

        $this->config->expects(self::once())
            ->method('isActive')
            ->willReturn(true);

        $this->guaranteeAbility->expects(self::once())
            ->method('isAvailable')
            ->with(self::equalTo($entityId))
            ->willReturn(true);

        $this->cancelingService->expects(self::once())
            ->method('cancelForOrder')
            ->with(self::equalTo($entityId));

        /** @var Observer|MockObject $observer */
        $observer = $this->getObserverMock($order);
        $this->observer->execute($observer);
    }

    /**
     * Gets mock object for observer.
     *
     * @param OrderInterface|null $order
     * @return Observer|MockObject
     */
    private function getObserverMock($order)
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDataByKey'])
            ->getMock();

        $observer->expects(self::any())
            ->method('getEvent')
            ->willReturn($event);

        $event->expects(self::any())
            ->method('getDataByKey')
            ->with('order')
            ->willReturn($order);

        return $observer;
    }
}
