<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Info as PaymentInfo;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PurchaseorderTest extends TestCase
{
    /**
     * @var Purchaseorder
     */
    private $object;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $eventManager = $this->getMockForAbstractClass(EventManagerInterface::class);
        $paymentDataMock = $this->createMock(PaymentHelper::class);
        $this->scopeConfigMock = $this->createPartialMock(
            ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $this->object = $objectManagerHelper->getObject(
            Purchaseorder::class,
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->scopeConfigMock,
            ]
        );
    }

    public function testAssignData()
    {
        $data = new DataObject([
            'po_number' => '12345'
        ]);

        $instance = $this->createMock(PaymentInfo::class);
        $this->object->setData('info_instance', $instance);
        $result = $this->object->assignData($data);
        $this->assertEquals($result, $this->object);
    }

    public function testValidate()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Purchase order number is a required field.');

        $data = new DataObject([]);

        $addressMock = $this->getMockForAbstractClass(OrderAddressInterface::class);
        $addressMock->expects($this->once())->method('getCountryId')->willReturn('UY');

        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn($addressMock);

        $instance = $this->createMock(Payment::class);

        $instance->expects($this->once())->method('getOrder')->willReturn($orderMock);

        $this->object->setData('info_instance', $instance);
        $this->object->assignData($data);

        $this->object->validate();
    }
}
