<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Store\Model\ScopeInterface;
use Magento\Payment\Test\Unit\Model\Method\AbstractMethod\Stub;

/**
 * Class AbstractMethodTest
 *
 * Test for class \Magento\Payment\Model\Method\AbstractMethod
 */
class AbstractMethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod
     */
    protected $payment;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();
        $this->quoteMock = $this->getMockBuilder('Magento\Quote\Api\Data\CartInterface')
            ->setMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $contextMock = $this->getMockBuilder('Magento\Framework\Model\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getEventDispatcher'])
            ->getMock();
        $contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);
        $this->loggerMock = $this->getMockBuilder('\Magento\Payment\Model\Method\Logger')
            ->setConstructorArgs([$this->getMockForAbstractClass('Psr\Log\LoggerInterface')])
            ->setMethods(['debug'])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->payment = $helper->getObject(
            Stub::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'context' => $contextMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testDebugData()
    {
        $debugData = ['masked' => '123'];
        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with($this->equalTo($debugData));

        $this->payment->debugData($debugData);
    }

    /**
     * @param bool $result
     *
     * @dataProvider dataProviderForTestIsAvailable
     */
    public function testIsAvailable($result)
    {
        $storeId = 15;
        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'payment/' . Stub::STUB_CODE . '/active',
                ScopeInterface::SCOPE_STORE,
                $storeId
            )->willReturn($result);

        $this->eventManagerMock->expects($result ? $this->once() : $this->never())
            ->method('dispatch')
            ->with(
                $this->equalTo('payment_method_is_active'),
                $this->countOf(3)
            );

        $this->assertEquals($result, $this->payment->isAvailable($this->quoteMock));
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsAvailable()
    {
        return [
            [
                'result' => true
            ],
            [
                'result' => false
            ],
        ];
    }
}
