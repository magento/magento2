<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Validator\MinimumOrderAmount;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidationMessageTest extends TestCase
{
    /**
     * @var ValidationMessage
     */
    private $model;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    /**
     * @var MockObject
     * @deprecated since 101.0.0
     * @see no alternatives
     */
    private $currencyMock;

    /**
     * @var MockObject
     */
    private $priceHelperMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->currencyMock = $this->getMockForAbstractClass(CurrencyInterface::class);
        $this->priceHelperMock = $this->createMock(Data::class);

        $this->model = new ValidationMessage(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->currencyMock,
            $this->priceHelperMock
        );
    }

    /**
     * @return void
     */
    public function testGetMessage(): void
    {
        $minimumAmount = 20;
        $minimumAmountCurrency = '$20';

        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($minimumAmount) {
                    if ($arg1 == 'sales/minimum_order/description' && $arg2 == ScopeInterface::SCOPE_STORE) {
                        return null;
                    } elseif ($arg1 == 'sales/minimum_order/amount' && $arg2 == ScopeInterface::SCOPE_STORE) {
                        return $minimumAmount;
                    }
                }
            );

        $this->priceHelperMock->expects($this->once())
            ->method('currency')
            ->with($minimumAmount, true, false)
            ->willReturn($minimumAmountCurrency);

        $this->assertEquals(__('Minimum order amount is %1', $minimumAmountCurrency), $this->model->getMessage());
    }

    /**
     * @return void
     */
    public function testGetConfigMessage(): void
    {
        $configMessage = 'config_message';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales/minimum_order/description', ScopeInterface::SCOPE_STORE)
            ->willReturn($configMessage);

        $message = $this->model->getMessage();

        $this->assertInstanceOf(Phrase::class, $message);
        $this->assertEquals($configMessage, $message->__toString());
    }
}
