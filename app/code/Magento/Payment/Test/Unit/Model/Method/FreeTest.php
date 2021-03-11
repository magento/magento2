<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Method;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FreeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Model\Method\Free */
    protected $methodFree;

    /**  @var \PHPUnit\Framework\MockObject\MockObject */
    protected $scopeConfig;

    /**  @var \PHPUnit\Framework\MockObject\MockObject */
    protected $currencyPrice;

    protected function setUp(): void
    {
        $paymentData  = $this->createMock(\Magento\Payment\Helper\Data::class);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->currencyPrice = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMock();

        $context = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $context->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);

        $registry = $this->createMock(\Magento\Framework\Registry::class);
        $extensionAttributesFactory = $this->createMock(\Magento\Framework\Api\ExtensionAttributesFactory::class);
        $customAttributeFactory = $this->createMock(\Magento\Framework\Api\AttributeValueFactory::class);

        $loggerMock = $this->getMockBuilder(\Magento\Payment\Model\Method\Logger::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class)])
            ->getMock();

        $this->methodFree = new \Magento\Payment\Model\Method\Free(
            $context,
            $registry,
            $extensionAttributesFactory,
            $customAttributeFactory,
            $paymentData,
            $this->scopeConfig,
            $loggerMock,
            $this->currencyPrice
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
        $this->scopeConfig->expects($this->at(0))
            ->method('getValue')
            ->willReturn($orderStatus);

        if ($orderStatus != 'pending') {
            $this->scopeConfig->expects($this->at(1))
                ->method('getValue')
                ->willReturn($paymentAction);
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
            $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
            $quote->expects($this->any())
                ->method('__call')
                ->with($this->equalTo('getGrandTotal'))
                ->willReturn($grandTotal);
        }

        $this->currencyPrice->expects($this->any())
            ->method('round')
            ->willReturnArgument(0);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn($isActive);

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
