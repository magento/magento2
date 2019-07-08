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

/**
 * Tests DataAssignObserver
 */
class DataAssignObserverTest extends TestCase
{
    public function testExecuteSetsProperData()
    {
        $additionalInfo = [
            'opaqueDataDescriptor' => 'foo',
            'opaqueDataValue' => 'bar',
            'ccLast4' => '1234'
        ];

        $observerContainer = $this->createMock(Observer::class);
        $event = $this->createMock(Event::class);
        $paymentInfoModel = $this->createMock(InfoInterface::class);
        $dataObject = new DataObject([PaymentInterface::KEY_ADDITIONAL_DATA => $additionalInfo]);
        $observerContainer->method('getEvent')
            ->willReturn($event);
        $event->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::MODEL_CODE, $paymentInfoModel],
                    [AbstractDataAssignObserver::DATA_CODE, $dataObject]
                ]
            );
        $paymentInfoModel->expects($this->at(0))
            ->method('setAdditionalInformation')
            ->with('opaqueDataDescriptor', 'foo');
        $paymentInfoModel->expects($this->at(1))
            ->method('setAdditionalInformation')
            ->with('opaqueDataValue', 'bar');
        $paymentInfoModel->expects($this->at(2))
            ->method('setAdditionalInformation')
            ->with('ccLast4', '1234');

        $observer = new DataAssignObserver();
        $observer->execute($observerContainer);
    }

    public function testDoestSetDataWhenEmpty()
    {
        $observerContainer = $this->createMock(Observer::class);
        $event = $this->createMock(Event::class);
        $paymentInfoModel = $this->createMock(InfoInterface::class);
        $observerContainer->method('getEvent')
            ->willReturn($event);
        $event->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::MODEL_CODE, $paymentInfoModel],
                    [AbstractDataAssignObserver::DATA_CODE, new DataObject()]
                ]
            );
        $paymentInfoModel->expects($this->never())
            ->method('setAdditionalInformation');

        $observer = new DataAssignObserver();
        $observer->execute($observerContainer);
    }
}
