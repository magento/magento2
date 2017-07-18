<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Billing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AbstractAgreementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Agreement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->paymentDataMock = $this->getMockBuilder(\Magento\Payment\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            \Magento\Paypal\Model\Billing\Agreement::class,
            ['paymentData' => $this->paymentDataMock]
        );
    }

    public function testGetPaymentMethodInstance()
    {
        $paymentMethodInstance = $this->getMockBuilder(\Magento\Payment\Model\Method\AbstractMethod::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStore'])
            ->getMockForAbstractClass();

        $paymentMethodInstance->expects($this->once())
            ->method('setStore');

        $this->paymentDataMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($paymentMethodInstance);

        $this->assertSame($paymentMethodInstance, $this->model->getPaymentMethodInstance());
    }
}
