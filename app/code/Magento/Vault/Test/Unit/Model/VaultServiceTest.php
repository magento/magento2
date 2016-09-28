<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Payment\Model\MethodInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Vault\Model\VaultService;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class VaultServiceTest extends \PHPUnit_Framework_TestCase
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
     * @var VaultService
     */
    private $vaultService;

    protected function setUp()
    {
        $this->paymentMethodList = $this->getMock(PaymentMethodListInterface::class);
        $this->instanceFactory = $this->getMockBuilder(InstanceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->vaultService = new VaultService($this->paymentMethodList, $this->instanceFactory);
    }

    /**
     * @covers \Magento\Vault\Model\VaultService::getActivePaymentList
     */
    public function testGetActivePaymentList()
    {
        $storeId = 1;
        $vaultPayment = $this->getMock(VaultPaymentInterface::class);
        $paymentMethodInterface1 = $this->getMock(PaymentMethodInterface::class);
        $paymentMethodInterface2 = $this->getMock(PaymentMethodInterface::class);
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
                [$paymentMethodInterface1, $this->getMock(MethodInterface::class)],
                [$paymentMethodInterface2, $vaultPayment]
            ]);

        $vaultPayments = $this->vaultService->getActivePaymentList($storeId);
        static::assertCount(1, $vaultPayments);
        static::assertInstanceOf(VaultPaymentInterface::class, $vaultPayment);
    }
}
