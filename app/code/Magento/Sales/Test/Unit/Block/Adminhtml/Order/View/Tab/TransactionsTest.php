<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab;

/**
 * Order transactions tab test
 */
class TransactionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\View\Tab\Transactions
     */
    protected $transactionsTab;

    /**
     * @var \Magento\Framework\Authorization|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $authorizationMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->authorizationMock = $this->createMock(\Magento\Framework\Authorization::class);
        $this->coreRegistryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->paymentMock = $this->createMock(\Magento\Sales\Model\Order\Payment::class);

        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('current_order')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->transactionsTab = $this->objectManager->getObject(
            \Magento\Sales\Block\Adminhtml\Order\View\Tab\Transactions::class,
            [
                'authorization' => $this->authorizationMock,
                'registry' => $this->coreRegistryMock
            ]
        );
    }

    public function testGetOrder()
    {
        $this->assertInstanceOf(\Magento\Sales\Model\Order::class, $this->transactionsTab->getOrder());
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
            [\Magento\Sales\Test\Unit\Block\Adminhtml\Order\View\Tab\Stub\OnlineMethod::class, true],
            [\Magento\OfflinePayments\Model\Cashondelivery::class, false],
            [\Magento\OfflinePayments\Model\Checkmo::class, false],
            [\Magento\OfflinePayments\Model\Banktransfer::class, false],
            [\Magento\OfflinePayments\Model\Purchaseorder::class, false]
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
