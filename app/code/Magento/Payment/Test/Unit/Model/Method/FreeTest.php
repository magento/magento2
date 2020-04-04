<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Free;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FreeTest extends TestCase
{
    /**
     * @var Free
     */
    private $methodFree;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * @var MockObject
     */
    private $currencyPriceMock;

    protected function setUp()
    {
        $paymentData = $this->createMock(Data::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->currencyPriceMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMock();

        $context = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->createMock(ManagerInterface::class);
        $context->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);

        $registry = $this->createMock(Registry::class);
        $extensionAttributesFactory = $this->createMock(ExtensionAttributesFactory::class);
        $customAttributeFactory = $this->createMock(AttributeValueFactory::class);

        $loggerMock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(LoggerInterface::class)])
            ->getMock();

        $this->methodFree = new Free(
            $context,
            $registry,
            $extensionAttributesFactory,
            $customAttributeFactory,
            $paymentData,
            $this->scopeConfigMock,
            $loggerMock,
            $this->currencyPriceMock
        );
    }

    /**
     * @param string $orderStatus
     * @param string $paymentAction
     * @param mixed $result
     * @dataProvider getConfigPaymentActionProvider
     */
    public function testGetConfigPaymentAction($orderStatus, $paymentAction, $result)
    {
        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->will($this->returnValue($orderStatus));

        if ($orderStatus != 'pending') {
            $this->scopeConfigMock->expects($this->at(1))
                ->method('getValue')
                ->will($this->returnValue($paymentAction));
        }
        $this->assertEquals($result, $this->methodFree->getConfigPaymentAction());
    }

    /**
     * @param float $grandTotal
     * @param bool $isActive
     * @param bool $notEmptyQuote
     * @param bool $result
     * @dataProvider getIsAvailableProvider
     */
    public function testIsAvailable($grandTotal, $isActive, $notEmptyQuote, $result)
    {
        $quote = null;
        if ($notEmptyQuote) {
            $quote = $this->createMock(Quote::class);
            $quote->expects($this->any())
                ->method('__call')
                ->with($this->equalTo('getGrandTotal'))
                ->will($this->returnValue($grandTotal));
        }

        $this->currencyPriceMock->expects($this->any())
            ->method('round')
            ->willReturnArgument(0);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($isActive));

        $this->assertEquals($result, $this->methodFree->isAvailable($quote));
    }

    /**
     * @return array
     */
    public function getIsAvailableProvider()
    {
        return [
            [0, true, true, true],
            [0.1, true, true, false],
            [0, false, false, false],
            [1, true, false, false],
            [0, true, false, false]
        ];
    }

    /**
     * @return array
     */
    public function getConfigPaymentActionProvider()
    {
        return [
            ['pending', 'action', null],
            ['processing', 'payment_action', 'payment_action']
        ];
    }
}
