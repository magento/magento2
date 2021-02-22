<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Validator\MinimumOrderAmount;

use Magento\Framework\Phrase;

class ValidationMessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     * @deprecated since 101.0.0
     */
    private $currencyMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $priceHelperMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->currencyMock = $this->createMock(\Magento\Framework\Locale\CurrencyInterface::class);
        $this->priceHelperMock = $this->createMock(\Magento\Framework\Pricing\Helper\Data::class);

        $this->model = new \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->currencyMock,
            $this->priceHelperMock
        );
    }

    public function testGetMessage()
    {
        $minimumAmount = 20;
        $minimumAmountCurrency = '$20';
        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with('sales/minimum_order/amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn($minimumAmount);

        $this->priceHelperMock->expects($this->once())
            ->method('currency')
            ->with($minimumAmount, true, false)
            ->willReturn($minimumAmountCurrency);

        $this->assertEquals(__('Minimum order amount is %1', $minimumAmountCurrency), $this->model->getMessage());
    }
    public function testGetConfigMessage()
    {
        $configMessage = 'config_message';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn($configMessage);

        $message = $this->model->getMessage();

        $this->assertEquals(Phrase::class, get_class($message));
        $this->assertEquals($configMessage, $message->__toString());
    }
}
