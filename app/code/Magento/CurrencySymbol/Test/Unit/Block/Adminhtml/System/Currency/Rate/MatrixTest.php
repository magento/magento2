<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Test\Unit\Block\Adminhtml\System\Currency\Rate;

use Magento\Backend\Model\Session;
use Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Matrix;
use Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

class MatrixTest extends TestCase
{
    /**
     * Object manager helper
     *
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
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

        $backendSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getRates', 'unsetData'])
            ->disableOriginalConstructor()
            ->getMock();
        $backendSessionMock->expects($this->once())->method('getRates')->willReturn($newRates);

        $currencyFactoryMock = $this->createPartialMock(CurrencyFactory::class, ['create']);
        $currencyMock = $this->createPartialMock(
            Currency::class,
            ['getConfigAllowCurrencies', 'getConfigBaseCurrencies', 'getCurrencyRates']
        );
        $currencyFactoryMock->expects($this->once())->method('create')->willReturn($currencyMock);
        $currencyMock->expects($this->once())->method('getConfigAllowCurrencies')->willReturn($allowCurrencies);
        $currencyMock->expects($this->once())->method('getConfigBaseCurrencies')->willReturn($baseCurrencies);
        $currencyMock->expects($this->once())
            ->method('getCurrencyRates')
            ->with($baseCurrencies, $allowCurrencies)
            ->willReturn($currencyRates);

        /** @var LayoutInterface|MockObject $layoutMock */
        $layoutMock = $this->getMockForAbstractClass(
            LayoutInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        /** @var Services $block */
        $block = $this->objectManagerHelper->getObject(
            Matrix::class,
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
