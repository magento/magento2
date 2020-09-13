<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Billing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Paypal\Model\Billing\Agreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractAgreementTest extends TestCase
{
    /**
     * @var Agreement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $paymentDataMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->paymentDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Agreement::class,
            ['paymentData' => $this->paymentDataMock]
        );
    }

    public function testGetPaymentMethodInstance()
    {
        $paymentMethodInstance = $this->getMockBuilder(AbstractMethod::class)
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
