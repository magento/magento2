<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Payment\Model\MethodInterface;
use Magento\Vault\Model\PaymentMethodList;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentMethodListTest extends TestCase
{
    /**
     * @var PaymentMethodListInterface|MockObject
     */
    private $paymentMethodList;

    /**
     * @var InstanceFactory|MockObject
     */
    private $instanceFactory;

    /**
     * @var PaymentMethodList
     */
    private $vaultPaymentList;

    protected function setUp(): void
    {
        $this->paymentMethodList = $this->getMockForAbstractClass(PaymentMethodListInterface::class);
        $this->instanceFactory = $this->getMockBuilder(InstanceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->vaultPaymentList = new PaymentMethodList($this->paymentMethodList, $this->instanceFactory);
    }

    /**
     * @covers \Magento\Vault\Model\PaymentMethodList::getActiveList
     */
    public function testGetActivePaymentList()
    {
        $storeId = 1;
        $vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        $paymentMethodInterface1 = $this->getMockForAbstractClass(PaymentMethodInterface::class);
        $paymentMethodInterface2 = $this->getMockForAbstractClass(PaymentMethodInterface::class);
        $activePayments = [
            $paymentMethodInterface1,
            $paymentMethodInterface2
        ];

        $this->paymentMethodList->expects(static::once())
            ->method('getActiveList')
            ->with($storeId)
            ->willReturn($activePayments);

        $this->instanceFactory->expects(static::exactly(2))
            ->method('create')
            ->willReturnMap([
                [$paymentMethodInterface1, $this->getMockForAbstractClass(MethodInterface::class)],
                [$paymentMethodInterface2, $vaultPayment]
            ]);

        $vaultPayments = $this->vaultPaymentList->getActiveList($storeId);
        static::assertCount(1, $vaultPayments);
        static::assertInstanceOf(VaultPaymentInterface::class, $vaultPayment);
    }
}
