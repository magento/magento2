<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order transactions tab test
 */
class TransactionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\View\Tab\Transactions
     */
    protected $transactionsTab;

    /**
     * @var \Magento\Framework\Authorization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->authorizationMock = $this->getMock('\Magento\Framework\Authorization', [], [], '', false);
        $this->coreRegistryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->orderMock = $this->getMock('\Magento\Sales\Model\Order', [], [], '', false);
        $this->paymentMock = $this->getMock('\Magento\Sales\Model\Order\Payment', [], [], '', false);

        $this->coreRegistryMock->expects($this->any())
            ->method('registry')
            ->with('current_order')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->transactionsTab = $this->objectManager->getObject(
            'Magento\Sales\Block\Adminhtml\Order\View\Tab\Transactions',
            [
                'authorization' => $this->authorizationMock,
                'registry' => $this->coreRegistryMock
            ]
        );
    }

    public function testGetOrder()
    {
        $this->assertInstanceOf('\Magento\Sales\Model\Order', $this->transactionsTab->getOrder());
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
            ['\Magento\Sales\Block\Adminhtml\Order\View\Tab\Stub\OnlineMethod', true],
            ['\Magento\OfflinePayments\Model\Cashondelivery', false],
            ['\Magento\OfflinePayments\Model\Checkmo', false],
            ['\Magento\OfflinePayments\Model\Banktransfer', false],
            ['\Magento\OfflinePayments\Model\Purchaseorder', false]
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
