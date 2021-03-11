<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System\Currency\Rate;

class MatrixTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object manager helper
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    protected function tearDown(): void
    {
        unset($this->objectManagerHelper);
    }

    public function testPrepareLayout()
    {
        $allowCurrencies = ['EUR', 'UAH', 'USD'];
        $baseCurrencies = ['USD'];
        $currencyRates = ['USD' => ['EUR' => -1, 'UAH' => 21.775, 'GBP' => 12, 'USD' => 1]];
        $expectedCurrencyRates = ['USD' => ['EUR' => null, 'UAH' => '21.7750', 'GBP' => '12.0000', 'USD' => '1.0000']];
        $newRates = ['USD' => ['EUR' => 0.7767, 'UAH' => 20, 'GBP' => 12, 'USD' => 1]];
        $expectedNewRates = ['USD' => ['EUR' => '0.7767', 'UAH' => '20.0000', 'GBP' => '12.0000', 'USD' => '1.0000']];

        $backendSessionMock = $this->createPartialMock(
            \Magento\Backend\Model\Session::class,
            ['getRates', 'unsetData']
        );
        $backendSessionMock->expects($this->once())->method('getRates')->willReturn($newRates);

        $currencyFactoryMock = $this->createPartialMock(\Magento\Directory\Model\CurrencyFactory::class, ['create']);
        $currencyMock = $this->createPartialMock(
            \Magento\Directory\Model\Currency::class,
            ['getConfigAllowCurrencies', 'getConfigBaseCurrencies', 'getCurrencyRates']
        );
        $currencyFactoryMock->expects($this->once())->method('create')->willReturn($currencyMock);
        $currencyMock->expects($this->once())->method('getConfigAllowCurrencies')->willReturn($allowCurrencies);
        $currencyMock->expects($this->once())->method('getConfigBaseCurrencies')->willReturn($baseCurrencies);
        $currencyMock->expects($this->once())
            ->method('getCurrencyRates')
            ->with($baseCurrencies, $allowCurrencies)
            ->willReturn($currencyRates);

        /** @var $layoutMock \Magento\Framework\View\LayoutInterface|\PHPUnit\Framework\MockObject\MockObject */
        $layoutMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\LayoutInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        /** @var $block \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services */
        $block = $this->objectManagerHelper->getObject(
            \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Matrix::class,
            [
                'dirCurrencyFactory' => $currencyFactoryMock,
                'backendSession' => $backendSessionMock
            ]
        );
        $block->setLayout($layoutMock);
        $this->assertEquals($allowCurrencies, $block->getAllowedCurrencies());
        $this->assertEquals($baseCurrencies, $block->getDefaultCurrencies());
        $this->assertEquals($expectedCurrencyRates, $block->getOldRates());
        $this->assertEquals($expectedNewRates, $block->getNewRates());
    }
}
