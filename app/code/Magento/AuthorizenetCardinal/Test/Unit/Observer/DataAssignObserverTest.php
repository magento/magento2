<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetCardinal\Test\Unit\Observer;

use Magento\AuthorizenetCardinal\Model\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\AuthorizenetCardinal\Observer\DataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class DataAssignObserverTest
 */
class DataAssignObserverTest extends TestCase
{
    /**
     * Tests setting JWT in payment additional information.
     */
    public function testExecuteSetsProperData()
    {
        $additionalInfo = [
            'cardinalJWT' => 'foo'
        ];

        $config = $this->createMock(Config::class);
        $config->method('isActive')
            ->willReturn(true);
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
        $paymentInfoModel->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('cardinalJWT', 'foo');

        $observer = new DataAssignObserver($config);
        $observer->execute($observerContainer);
    }

    /**
     * Tests case when Cardinal JWT is absent.
     */
    public function testDoesntSetDataWhenEmpty()
    {
        $config = $this->createMock(Config::class);
        $config->method('isActive')
            ->willReturn(true);
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

        $observer = new DataAssignObserver($config);
        $observer->execute($observerContainer);
    }

    /**
     * Tests case when CardinalCommerce is disabled.
     */
    public function testDoesntSetDataWhenDisabled()
    {
        $config = $this->createMock(Config::class);
        $config->method('isActive')
            ->willReturn(false);
        $observerContainer = $this->createMock(Observer::class);
        $observerContainer->expects($this->never())
            ->method('getEvent');
        $observer = new DataAssignObserver($config);
        $observer->execute($observerContainer);
    }
}
