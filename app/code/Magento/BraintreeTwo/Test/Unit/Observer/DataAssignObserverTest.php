<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\BraintreeTwo\Observer\DataAssignObserver;

/**
 * Class DataAssignObserverTest
 */
class DataAssignObserverTest extends \PHPUnit_Framework_TestCase
{
    const PAYMENT_METHOD_NONCE = 'nonce';

    public function testExecute()
    {
        $observerContainer = $this->getMockBuilder(Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethodFacade = $this->getMock(MethodInterface::class);
        $paymentInfoModel = $this->getMock(InfoInterface::class);
        $dataObject = new DataObject(
            [
                'payment_method_nonce' => self::PAYMENT_METHOD_NONCE
            ]
        );
        $observerContainer->expects(static::atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects(static::exactly(2))
            ->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::METHOD_CODE, $paymentMethodFacade],
                    [AbstractDataAssignObserver::DATA_CODE, $dataObject]
                ]
            );
        $paymentMethodFacade->expects(static::once())
            ->method('getInfoInstance')
            ->willReturn($paymentInfoModel);
        $paymentInfoModel->expects(static::once())
            ->method('setAdditionalInformation')
            ->with(
                'payment_method_nonce',
                self::PAYMENT_METHOD_NONCE
            );
        $observer = new DataAssignObserver();
        $observer->execute($observerContainer);
    }
}
