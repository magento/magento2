<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab;

use Magento\Framework\Authorization;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\OfflinePayments\Model\Purchaseorder;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Transactions;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab\Stub\OnlineMethod;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Order transactions tab test
 */
class TransactionsTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Transactions
     */
    protected $transactionsTab;

    /**
     * @var Authorization|MockObject
     */
    protected $authorizationMock;

    /**
     * @var Registry|MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var Payment|MockObject
     */
    protected $paymentMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->authorizationMock = $this->createMock(Authorization::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->paymentMock = $this->createMock(Payment::class);

        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('current_order')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->transactionsTab = $this->objectManager->getObject(
            Transactions::class,
            [
                'authorization' => $this->authorizationMock,
                'registry' => $this->coreRegistryMock
            ]
        );
    }

    public function testGetOrder()
    {
        $this->assertInstanceOf(Order::class, $this->transactionsTab->getOrder());
    }

    /**
     * @param string $methodClass
     * @param bool $expectedResult
     * @depends testGetOrder
     * @dataProvider canShowTabDataProvider
     */
    public function testCanShowTab($methodClass, $expectedResult)
    {
        $methodInstance = $this->objectManager->getObject($methodClass);
        $this->paymentMock->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn($methodInstance);

        $this->assertEquals($expectedResult, $this->transactionsTab->canShowTab());
    }

    /**
     * @return array
     */
    public function canShowTabDataProvider()
    {
        return [
            [OnlineMethod::class, true],
            [Cashondelivery::class, false],
            [Checkmo::class, false],
            [Banktransfer::class, false],
            [Purchaseorder::class, false]
        ];
    }

    /**
     * @param bool $isAllowed
     * @param bool $expectedResult
     * @dataProvider isHiddenDataProvider
     */
    public function testIsHidden($isAllowed, $expectedResult)
    {
        $this->authorizationMock->expects($this->any())
            ->method('isAllowed')
            ->with('Magento_Sales::transactions_fetch')
            ->willReturn($isAllowed);

        $this->assertEquals($expectedResult, $this->transactionsTab->isHidden());
    }

    /**
     * @return array
     */
    public function isHiddenDataProvider()
    {
        return [
            [true, false],
            [false, true]
        ];
    }
}
