<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Validator\MinimumOrderAmount;

use Magento\Framework\Phrase;

class ValidationMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->currencyMock = $this->getMock(\Magento\Framework\Locale\CurrencyInterface::class);

        $this->model = new \Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->currencyMock
        );
    }

    public function testGetMessage()
    {
        $minimumAmount = 20;
        $minimumAmountCurrency = '$20';
        $currencyCode = 'currency_code';

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with('sales/minimum_order/amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn($minimumAmount);

        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, ['getCurrentCurrencyCode'], [], '', false);
        $storeMock->expects($this->once())->method('getCurrentCurrencyCode')->willReturn($currencyCode);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);


        $currencyMock = $this->getMock(\Magento\Framework\Currency::class, [], [], '', false);
        $this->currencyMock->expects($this->once())
            ->method('getCurrency')
            ->with($currencyCode)
            ->willReturn($currencyMock);

        $currencyMock->expects($this->once())
            ->method('toCurrency')
            ->with($minimumAmount)
            ->willReturn($minimumAmountCurrency);

        $this->assertEquals(
            __('Minimum order amount is %1', $minimumAmountCurrency),
            $this->model->getMessage()
        );
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
