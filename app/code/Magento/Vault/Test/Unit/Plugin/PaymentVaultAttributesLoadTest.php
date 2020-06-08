<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Plugin\PaymentVaultAttributesLoad;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\Vault\Plugin\PaymentVaultAttributesLoad
 */
class PaymentVaultAttributesLoadTest extends TestCase
{
    /**
     * @var OrderPaymentExtensionFactory|MockObject
     */
    private $paymentExtensionFactoryMock;

    /**
     * @var PaymentTokenManagementInterface|MockObject
     */
    private $paymentTokenManagementMock;

    /**
     * @var OrderPaymentInterface|MockObject
     */
    private $paymentMock;

    /**
     * @var OrderPaymentExtensionInterface|MockObject
     */
    private $paymentExtensionMock;

    /**
     * @var PaymentVaultAttributesLoad
     */
    private $plugin;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->paymentMock = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId', 'setExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->paymentExtensionMock = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getVaultPaymentToken', 'setVaultPaymentToken'])
            ->getMockForAbstractClass();

        $this->paymentExtensionFactoryMock = $this->createMock(OrderPaymentExtensionFactory::class);
        $this->paymentTokenManagementMock = $this->getMockBuilder(PaymentTokenManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByPaymentId'])
            ->getMockForAbstractClass();

        $this->plugin = (new ObjectManagerHelper($this))->getObject(
            PaymentVaultAttributesLoad::class,
            [
                'paymentExtensionFactory' => $this->paymentExtensionFactoryMock,
                'paymentTokenManagement' => $this->paymentTokenManagementMock
            ]
        );
    }

    /**
     * Test case when paymentExtension param was not provided.
     */
    public function testAfterGetExtensionAttributesCallsFactoryIfPaymentExtensionIsNull(): void
    {
        $this->paymentExtensionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->paymentExtensionMock);

        $this->assertSame(
            $this->paymentExtensionMock,
            $this->plugin->afterGetExtensionAttributes($this->paymentMock, null)
        );
    }

    /**
     * Test case when payment token was already set.
     */
    public function testAfterGetExtensionAttributesWhenPaymentTokenIsNotNull(): void
    {
        $this->paymentExtensionMock->expects($this->once())
            ->method('getVaultPaymentToken')
            ->willReturn($this->getMockForAbstractClass(PaymentTokenInterface::class));
        $this->paymentTokenManagementMock->expects($this->never())->method('getByPaymentId');
        $this->paymentMock->expects($this->never())->method('setExtensionAttributes');
        $this->assertSame(
            $this->paymentExtensionMock,
            $this->plugin->afterGetExtensionAttributes($this->paymentMock, $this->paymentExtensionMock)
        );
    }

    /**
     * Test case when payment token is null and extension attributes must be set.
     */
    public function testAfterGetExtensionAttributesWhenPaymentTokenIsNull(): void
    {
        $this->paymentExtensionMock->expects($this->once())->method('getVaultPaymentToken')->willReturn(null);

        $paymentTokenMock = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $this->paymentTokenManagementMock->expects($this->once())
            ->method('getByPaymentId')
            ->willReturn($paymentTokenMock);
        $this->paymentExtensionMock->expects($this->once())
            ->method('setVaultPaymentToken')
            ->with($paymentTokenMock);
        $this->paymentMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->paymentExtensionMock);

        $this->assertSame(
            $this->paymentExtensionMock,
            $this->plugin->afterGetExtensionAttributes($this->paymentMock, $this->paymentExtensionMock)
        );
    }
}
