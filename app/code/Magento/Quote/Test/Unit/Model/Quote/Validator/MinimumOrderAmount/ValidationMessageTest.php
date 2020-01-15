<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Validator\MinimumOrderAmount;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidationMessageTest extends TestCase
{
    /**
     * @var ValidationMessage
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Data|MockObject
     */
    private $priceHelperMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->priceHelperMock = $this->createMock(Data::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ValidationMessage::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'priceHelper' => $this->priceHelperMock
            ]
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
            ->willReturn($minimumAmountCurrency);

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

        $this->assertInstanceOf(Phrase::class, $message);
        $this->assertEquals($configMessage, $message->__toString());
    }
}
