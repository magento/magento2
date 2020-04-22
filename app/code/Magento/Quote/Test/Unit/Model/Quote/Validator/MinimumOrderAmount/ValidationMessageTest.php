<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     */
    private $currencyMock;

    /**
     * @var MockObject
     */
    private $priceHelperMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->currencyMock = $this->createMock(CurrencyInterface::class);
        $this->priceHelperMock = $this->createMock(Data::class);

        $this->model = new ValidationMessage(
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
            ->with('sales/minimum_order/description', ScopeInterface::SCOPE_STORE)
            ->willReturn(null);

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with('sales/minimum_order/amount', ScopeInterface::SCOPE_STORE)
            ->willReturn($minimumAmount);

        $this->priceHelperMock->expects($this->once())
            ->method('currency')
            ->with($minimumAmount, true, false)
            ->will($this->returnValue($minimumAmountCurrency));

        $this->assertEquals(__('Minimum order amount is %1', $minimumAmountCurrency), $this->model->getMessage());
    }
    public function testGetConfigMessage()
    {
        $configMessage = 'config_message';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales/minimum_order/description', ScopeInterface::SCOPE_STORE)
            ->willReturn($configMessage);

        $message = $this->model->getMessage();

        $this->assertEquals(Phrase::class, get_class($message));
        $this->assertEquals($configMessage, $message->__toString());
    }
}
