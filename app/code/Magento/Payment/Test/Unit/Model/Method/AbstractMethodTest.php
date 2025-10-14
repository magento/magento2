<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Payment\Test\Unit\Model\Method\AbstractMethod\Stub;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractMethodTest
 *
 * Test for class \Magento\Payment\Model\Method\AbstractMethod
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractMethodTest extends TestCase
{
    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod
     */
    protected $payment;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var CartInterface|MockObject
     */
    protected $quoteMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->onlyMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->onlyMethods(['dispatch'])
            ->getMockForAbstractClass();
        $this->quoteMock = $this->getMockBuilder(CartInterface::class)
            ->onlyMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEventDispatcher'])
            ->getMock();
        $contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(LoggerInterface::class)])
            ->onlyMethods(['debug'])
            ->getMock();

        $helper = new ObjectManager($this);
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
            ->with($debugData);

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
                'payment_method_is_active',
                $this->countOf(3)
            );

        $this->assertEquals($result, $this->payment->isAvailable($this->quoteMock));
    }

    public function testAssignData()
    {
        $data = new DataObject();
        $paymentInfo = $this->getMockForAbstractClass(InfoInterface::class);

        $this->payment->setInfoInstance($paymentInfo);

        $eventData = [
            AbstractDataAssignObserver::METHOD_CODE => $this,
            AbstractDataAssignObserver::MODEL_CODE => $paymentInfo,
            AbstractDataAssignObserver::DATA_CODE => $data
        ];

        $this->eventManagerMock->expects(static::exactly(2))
            ->method('dispatch')
            ->willReturnMap(
                [
                    [
                        'payment_method_assign_data_' . Stub::STUB_CODE,
                        $eventData
                    ],
                    [
                        'payment_method_assign_data',
                        $eventData
                    ]
                ]
            );

        $this->payment->assignData($data);
    }

    /**
     * @return array
     */
    public static function dataProviderForTestIsAvailable()
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
