<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
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
use PHPUnit\Framework\Attributes\DataProvider;
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
        $objectManagerMock = $this->createMock(ObjectManager::class);
        ObjectManager::setInstance($objectManagerMock);

        $paymentData  = $this->createMock(Data::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->currencyPrice = $this->createMock(PriceCurrencyInterface::class);

        $context = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->createMock(ManagerInterface::class);
        $context->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);

        $registry = $this->createMock(Registry::class);
        $extensionAttributesFactory = $this->createMock(ExtensionAttributesFactory::class);
        $customAttributeFactory = $this->createMock(AttributeValueFactory::class);

        $loggerMock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs([$this->createMock(LoggerInterface::class)])
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
     * Test get config payment action
     *
     * @param string $orderStatus
     * @param string $paymentAction
     * @param string|null $result
     * @return void
     */
    #[DataProvider('getConfigPaymentActionProvider')]
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
     * Test is available
     *
     * @param float $grandTotal
     * @param bool $isActive
     * @param bool $notEmptyQuote
     * @param bool $result
     * @return void
     */
    #[DataProvider('getIsAvailableProvider')]
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
