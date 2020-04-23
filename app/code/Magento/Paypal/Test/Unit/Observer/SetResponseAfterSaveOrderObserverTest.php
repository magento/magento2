<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Framework\App\ViewInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Paypal\Helper\Hss;
use Magento\Paypal\Observer\SetResponseAfterSaveOrderObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetResponseAfterSaveOrderObserverTest extends TestCase
{
    /**
     * @var SetResponseAfterSaveOrderObserver
     */
    protected $_model;

    /**
     * @var Observer
     */
    protected $_observer;

    /**
     * @var DataObject
     */
    protected $_event;

    /**
     * @var Registry|MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var Hss|MockObject
     */
    protected $paypalHssMock;

    /**
     * @var ViewInterface|MockObject
     */
    protected $viewMock;

    protected function setUp(): void
    {
        $this->_event = new DataObject();

        $this->_observer = new Observer();
        $this->_observer->setEvent($this->_event);

        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->paypalHssMock = $this->createPartialMock(Hss::class, ['getHssMethods']);
        $this->viewMock = $this->getMockForAbstractClass(
            ViewInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            SetResponseAfterSaveOrderObserver::class,
            [
                'coreRegistry' => $this->coreRegistryMock,
                'paypalHss' => $this->paypalHssMock,
                'view' => $this->viewMock,
            ]
        );
    }

    /**
     * Get data for test testSetResponseAfterSaveOrderSuccess
     *
     * @return array
     */
    protected function getSetResponseAfterSaveOrderTestData()
    {
        $iFrameHtml = 'iframe-html';
        $paymentMethod = 'method-2';

        return [
            'order.getId' => 10,
            'payment.getMethod' => $paymentMethod,
            'paypalHss.getHssMethods' => [
                'method-1',
                $paymentMethod,
                'method-3'
            ],
            'result.getData' => [
                'error' => false
            ],
            'block.toHtml' => $iFrameHtml,
            'result.setData' => [
                'error' => false,
                'update_section' => [
                    'name' => 'paypaliframe',
                    'html' => $iFrameHtml
                ],
                'redirect' => false,
                'success' => false,
            ]
        ];
    }

    /**
     * Run setResponseAfterSaveOrder method test
     *
     * @return void
     */
    public function testSetResponseAfterSaveOrderSuccess()
    {
        $testData = $this->getSetResponseAfterSaveOrderTestData();

        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $blockMock = $this->getMockBuilder(BlockInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('hss_order')
            ->willReturn($orderMock);

        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($testData['order.getId']);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($testData['payment.getMethod']);

        $this->paypalHssMock->expects($this->once())
            ->method('getHssMethods')
            ->willReturn($testData['paypalHss.getHssMethods']);

        $observerMock->expects($this->atLeastOnce())
            ->method('getData')
            ->with('result')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())
            ->method('getData')
            ->willReturn($testData['result.getData']);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->with('checkout_onepage_review', true, true, false);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('paypal.iframe')
            ->willReturn($blockMock);

        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($testData['block.toHtml']);

        $resultMock->expects($this->once())
            ->method('setData')
            ->with($testData['result.setData']);

        $this->_model->execute($observerMock);
    }
}
