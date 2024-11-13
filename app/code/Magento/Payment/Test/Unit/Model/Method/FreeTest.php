<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    protected $methodFree;

    /**
     * @var MockObject
     */
    protected $scopeConfig;

    /**
     * @var MockObject
     */
    protected $currencyPrice;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $paymentData  = $this->createMock(Data::class);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->currencyPrice = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMock();

        $context = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
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
            $this->scopeConfig,
            $loggerMock,
            $this->currencyPrice
        );
    }

    /**
     * @param string $orderStatus
     * @param string $paymentAction
     * @param mixed $result
     *
     * @return void
     * @dataProvider getConfigPaymentActionProvider
     */
    public function testGetConfigPaymentAction($orderStatus, $paymentAction, $result): void
    {

        if ($orderStatus != 'pending') {
            $this->scopeConfig
                ->method('getValue')
                ->willReturnOnConsecutiveCalls($orderStatus, $paymentAction);
        }
        $this->assertEquals($result, $this->methodFree->getConfigPaymentAction());
    }

    /**
     * @param float $grandTotal
     * @param bool $isActive
     * @param bool $notEmptyQuote
     * @param bool $result
     *
     * @return void
     * @dataProvider getIsAvailableProvider
     */
    public function testIsAvailable(
        float $grandTotal,
        bool $isActive,
        bool $notEmptyQuote,
        bool $result
    ): void {
        $quote = null;
        if ($notEmptyQuote) {
            $quote = $this->createMock(Quote::class);
            $quote->expects($this->any())
                ->method('__call')
                ->with('getGrandTotal')
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
    public static function getIsAvailableProvider(): array
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
    public static function getConfigPaymentActionProvider(): array
    {
        return [
            ['pending', 'action', null],
            ['processing', 'payment_action', 'payment_action']
        ];
    }
}
