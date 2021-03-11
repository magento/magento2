<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Info as PaymentInfo;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;

class PurchaseorderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Purchaseorder
     */
    protected $_object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_scopeConfig;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $eventManager = $this->getMockForAbstractClass(EventManagerInterface::class);
        $paymentDataMock = $this->createMock(PaymentHelper::class);
        $this->_scopeConfig = $this->createPartialMock(
            ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $this->_object = $objectManagerHelper->getObject(
            Purchaseorder::class,
            [
                'eventManager' => $eventManager,
                'paymentData' => $paymentDataMock,
                'scopeConfig' => $this->_scopeConfig,
            ]
        );
    }

    public function testAssignData()
    {
        $data = new DataObject([
            'po_number' => '12345'
        ]);

        $instance = $this->createMock(PaymentInfo::class);
        $this->_object->setData('info_instance', $instance);
        $result = $this->_object->assignData($data);
        $this->assertEquals($result, $this->_object);
    }

    /**
     */
    public function testValidate()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Purchase order number is a required field.');

        $data = new DataObject([]);

        $addressMock = $this->getMockForAbstractClass(OrderAddressInterface::class);
        $addressMock->expects($this->once())->method('getCountryId')->willReturn('UY');

        $orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn($addressMock);

        $instance = $this->createMock(Payment::class);

        $instance->expects($this->once())->method('getOrder')->willReturn($orderMock);

        $this->_object->setData('info_instance', $instance);
        $this->_object->assignData($data);

        $this->_object->validate();
    }
}
