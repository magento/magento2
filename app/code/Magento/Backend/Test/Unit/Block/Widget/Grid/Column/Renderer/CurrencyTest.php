<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Renderer;

use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Framework\App\RequestInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency;
use Magento\Framework\Locale\Currency as LocaleCurrency;
use Magento\Directory\Model\Currency as CurrencyData;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrencyTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Currency
     */
    private $currencyRenderer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Store|MockObject
     */
    private $storeManagerMock;

    /**
     * @var DefaultLocator|MockObject
     */
    private $currencyLocatorMock;

    /**
     * @var CurrencyFactory|MockObject
     */
    private $currencyFactoryMock;

    /**
     * @var LocaleCurrency|MockObject
     */
    private $localeCurrencyMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var Column|MockObject
     */
    private $columnMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->currencyLocatorMock = $this->createMock(DefaultLocator::class);
        $this->currencyFactoryMock = $this->createMock(CurrencyFactory::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $defaultCurrencyCode = 'USD';
        $currencyMock = $this->createMock(CurrencyData::class);
        $this->currencyFactoryMock->method('create')
            ->willReturn($currencyMock);
        $currencyMock->method('load')
            ->with($defaultCurrencyCode)
            ->willReturnSelf();
        $this->currencyLocatorMock->method('getDefaultCurrency')
            ->with($this->requestMock)
            ->willReturn($defaultCurrencyCode);
        $this->columnMock = $this->createPartialMockWithReflection(
            Column::class,
            ['getIndex', 'getShowNumberSign', 'getDefault']
        );
        $this->columnMock->method('getIndex')->willReturn('value');
        $this->columnMock->method('getShowNumberSign')->willReturn(false);
        $this->columnMock->method('getDefault')->willReturn('');
        $this->localeCurrencyMock = $this->createPartialMockWithReflection(
            LocaleCurrency::class,
            ['getCurrency', 'toCurrency']
        );
        $this->currencyRenderer = $this->objectManager->getObject(
            Currency::class,
            [
                'storeManager' => $this->storeManagerMock,
                'localeCurrency' => $this->localeCurrencyMock,
                'currencyLocator' => $this->currencyLocatorMock,
                'request' => $this->requestMock,
                'currencyFactory' => $this->currencyFactoryMock
            ]
        );
    }

    public function testRenderWithDefaultCurrency()
    {
        $defaultCurrencyCode = 'USD';
        $amount = 123.45;
        $formattedAmount = '$123.45';
        $row = new DataObject(['value' => $amount]);
        $this->currencyRenderer->setColumn($this->columnMock);
        $this->localeCurrencyMock->method('getCurrency')
            ->with($defaultCurrencyCode)
            ->willReturn($this->localeCurrencyMock);
        $this->localeCurrencyMock->method('toCurrency')
            ->with(sprintf("%f", $amount))
            ->willReturn($formattedAmount);
        $result = $this->currencyRenderer->render($row);
        $this->assertEquals($formattedAmount, $result);
    }

    public function testRenderWithNonDefaultCurrency()
    {
        $nonDefaultCurrencyCode = 'EUR';
        $amount = 123.45;
        $formattedAmount = '€123.45';
        $storeId = 2;
        $row = new DataObject([
            'value' => $amount,
            'store_id' => $storeId
        ]);
        $this->currencyRenderer->setColumn($this->columnMock);
        $storeMock = $this->createPartialMock(
            Store::class,
            ['getCurrentCurrencyCode']
        );
        $this->storeManagerMock->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);
        $storeMock->method('getCurrentCurrencyCode')
            ->willReturn($nonDefaultCurrencyCode);
        $this->localeCurrencyMock->method('getCurrency')
            ->with($nonDefaultCurrencyCode)
            ->willReturn($this->localeCurrencyMock);
        $this->localeCurrencyMock->method('toCurrency')
            ->with(sprintf("%f", $amount))
            ->willReturn($formattedAmount);
        $result = $this->currencyRenderer->render($row);
        $this->assertEquals($formattedAmount, $result);
    }
}
