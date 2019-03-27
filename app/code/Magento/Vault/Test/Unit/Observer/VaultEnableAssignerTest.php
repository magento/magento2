<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Vault\Observer\VaultEnableAssigner;
use PHPUnit\Framework\MockObject\MockObject;

class VaultEnableAssignerTest extends \PHPUnit\Framework\TestCase
{
    public function testExecuteNoActiveCode()
    {
        $dataObject = new DataObject();

        $observer = $this->getPreparedObserverWithMap(
            [
                [AbstractDataAssignObserver::DATA_CODE, $dataObject]
            ]
        );

        $vaultEnableAssigner = new VaultEnableAssigner();

        $vaultEnableAssigner->execute($observer);
    }

    /**
     * @param string $activeCode
     * @param boolean $expectedBool
     * @dataProvider booleanDataProvider
     */
    public function testExecute($activeCode, $expectedBool)
    {
        $dataObject = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    VaultConfigProvider::IS_ACTIVE_CODE => $activeCode
                ]
            ]
        );
        $paymentModel = $this->createMock(InfoInterface::class);

        $paymentModel->method('setAdditionalInformation')
            ->with(
                VaultConfigProvider::IS_ACTIVE_CODE,
                $expectedBool
            );

        $observer = $this->getPreparedObserverWithMap(
            [
                [AbstractDataAssignObserver::DATA_CODE, $dataObject],
                [AbstractDataAssignObserver::MODEL_CODE, $paymentModel]
            ]
        );

        $vaultEnableAssigner = new VaultEnableAssigner();

        $vaultEnableAssigner->execute($observer);
    }

    /**
     * @return array
     */
    public function booleanDataProvider()
    {
        return [
            ['true', true],
            ['1', true],
            ['on', true],
            ['false', false],
            ['0', false],
            ['off', false],
            [null, false]
        ];
    }

    /**
     * @param array $returnMap
     * @return MockObject|Observer
     */
    private function getPreparedObserverWithMap(array $returnMap)
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects(self::atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects(self::atLeastOnce())
            ->method('getDataByKey')
            ->willReturnMap(
                $returnMap
            );

        return $observer;
    }
}
