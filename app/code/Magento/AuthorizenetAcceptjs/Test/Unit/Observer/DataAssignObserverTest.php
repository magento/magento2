<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\AuthorizenetAcceptjs\Observer\DataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for Magento\AuthorizenetAcceptjs\Observer\DataAssignObserver
 */
class DataAssignObserverTest extends TestCase
{
    /**
     * @var Observer|MockObject
     */
    private $observerContainer;

    /**
     * @var Event|MockObject
     */
    private $event;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentInfoModel;

    /**
     * @var DataAssignObserver
     */
    private $observer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->observerContainer = $this->createMock(Observer::class);
        $this->event = $this->createMock(Event::class);
        $this->paymentInfoModel = $this->createMock(InfoInterface::class);
        $this->observerContainer->method('getEvent')
            ->willReturn($this->event);
        $this->observer = new DataAssignObserver();
    }

    /**
     * @return void
     */
    public function testExecuteSetsProperData()
    {
        $additionalInfo = [
            'opaqueDataDescriptor' => 'foo',
            'opaqueDataValue' => 'bar',
            'ccLast4' => '1234'
        ];
        $dataObject = new DataObject([
            PaymentInterface::KEY_ADDITIONAL_DATA => $additionalInfo
        ]);
        $this->event->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::MODEL_CODE, $this->paymentInfoModel],
                    [AbstractDataAssignObserver::DATA_CODE, $dataObject],
                ]
            );
        $this->paymentInfoModel->expects($this->at(0))
            ->method('setAdditionalInformation')
            ->with('opaqueDataDescriptor', 'foo');
        $this->paymentInfoModel->expects($this->at(1))
            ->method('setAdditionalInformation')
            ->with('opaqueDataValue', 'bar');
        $this->paymentInfoModel->expects($this->at(2))
            ->method('setAdditionalInformation')
            ->with('ccLast4', '1234');

        $this->observer->execute($this->observerContainer);
    }

    /**
     * @return void
     */
    public function testDoestSetDataWhenEmpty()
    {
        $this->event->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::MODEL_CODE, $this->paymentInfoModel],
                    [AbstractDataAssignObserver::DATA_CODE, new DataObject()],
                ]
            );
        $this->paymentInfoModel->expects($this->never())
            ->method('setAdditionalInformation');

        $this->observer->execute($this->observerContainer);
    }
}
