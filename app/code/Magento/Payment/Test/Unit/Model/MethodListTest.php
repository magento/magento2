<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Api\PaymentMethodListInterface;
use Magento\Payment\Model\Checks\Composite;
use Magento\Payment\Model\Checks\SpecificationFactory;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\InstanceFactory;
use Magento\Payment\Model\MethodList;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MethodListTest extends TestCase
{
    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PaymentMethodListInterface|MockObject
     */
    private $paymentMethodList;

    /**
     * @var InstanceFactory|MockObject
     */
    private $paymentMethodInstanceFactory;

    /**
     * @var MockObject
     */
    protected $specificationFactoryMock;

    /**
     * @var array $additionalChecks
     */
    private $additionalChecks;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->paymentMethodList = $this->getMockBuilder(PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentMethodInstanceFactory = $this->getMockBuilder(
            InstanceFactory::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->specificationFactoryMock = $this->createMock(SpecificationFactory::class);

        $this->additionalChecks = ['acme_custom_payment_method_check' => 'acme_custom_payment_method_check'];

        $this->methodList = $this->objectManager->getObject(
            MethodList::class,
            [
                'specificationFactory' => $this->specificationFactoryMock,
                'additionalChecks' => $this->additionalChecks
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $this->methodList,
            'paymentMethodList',
            $this->paymentMethodList
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->methodList,
            'paymentMethodInstanceFactory',
            $this->paymentMethodInstanceFactory
        );
    }

    /**
     * Verify available payment methods
     */
    public function testGetAvailableMethods()
    {
        $storeId = 1;
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->willReturn($this->createMock(Payment::class));

        $methodInstanceMock = $this->createMock(AbstractMethod::class);
        $methodInstanceMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $compositeMock = $this->createMock(Composite::class);
        $compositeMock->expects($this->atLeastOnce())
            ->method('isApplicable')
            ->with($methodInstanceMock, $quoteMock)
            ->willReturn(true);

        $this->specificationFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with(
                array_merge(
                    [
                        AbstractMethod::CHECK_USE_CHECKOUT,
                        AbstractMethod::CHECK_USE_FOR_COUNTRY,
                        AbstractMethod::CHECK_USE_FOR_CURRENCY,
                        AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX
                    ],
                    $this->additionalChecks
                )
            )->willReturn($compositeMock);

        $methodMock = $this->getMockForAbstractClass(PaymentMethodInterface::class);
        $this->paymentMethodList->expects($this->once())
            ->method('getActiveList')
            ->willReturn([$methodMock]);
        $this->paymentMethodInstanceFactory->expects($this->once())
            ->method('create')
            ->willReturn($methodInstanceMock);

        $methodInstanceMock->expects($this->atLeastOnce())
            ->method('setInfoInstance')
            ->with($this->createMock(Payment::class))->willReturnSelf();

        $this->assertEquals([$methodInstanceMock], $this->methodList->getAvailableMethods($quoteMock));
    }
}
