<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Directory\Model\Currency\DefaultLocator;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class Currency.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[\AllowDynamicProperties] //@phpstan-ignore-line
class CurrencyTest extends TestCase
{
    /**
     * @var Currency|MockObject
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var DefaultLocator|MockObject
     */
    private $currencyLocatorMock;

    /**
     * @var CurrencyInterface|MockObject
     */
    private $localeCurrencyMock;

    /**
     * @var Column|MockObject
     */
    private $gridColumnMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var DataObject
     */
    private $row;

    /**
     * @var CurrencyModel|MockObject
     */
    private $currencyMock;

    /**
     * @var MockObject
     */
    private $backendCurrencyMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(
            ScopeConfigInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getValue']
        );

        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStore', 'getWebsite']
        );

        $this->currencyLocatorMock = $this->getMockBuilder(DefaultLocator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyMock = $this->createMock(CurrencyModel::class);
        $this->currencyMock->expects($this->any())->method('load')->willReturnSelf();

        $currencyFactoryMock = $this->createPartialMock(CurrencyFactory::class, ['create']);
        $currencyFactoryMock->expects($this->any())->method('create')->willReturn($this->currencyMock);

        $this->backendCurrencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeCurrencyMock = $this->getMockForAbstractClass(
            CurrencyInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getCurrency']
        );

        $this->gridColumnMock = $this->getMockBuilder(Column::class)
            ->addMethods(['getIndex', 'getRateField', 'getCurrency'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            Currency::class,
            [
                '_scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'currencyLocator' => $this->currencyLocatorMock,
                'currencyFactory' => $currencyFactoryMock,
                'localeCurrency' => $this->localeCurrencyMock
            ]
        );
        $this->model->setColumn($this->gridColumnMock);
    }

    /**
     * Test render function which converts store currency based on price scope settings
     *
     * @param float $rate
     * @param string $columnIndex
     * @param string $storeCurrencyCode
     * @param string $adminOrderAmount
     * @param string $convertedAmount
     * @throws LocalizedException
     * @throws CurrencyException|Exception
     * @dataProvider getCurrencyDataProvider
     */
    public function testRender(
        float $rate,
        string $columnIndex,
        string $storeCurrencyCode,
        string $adminOrderAmount,
        string $convertedAmount,
    ): void {
        $this->row = new DataObject(
            [
                $columnIndex => $adminOrderAmount,
                'rate' => $rate
            ]
        );
        $this->backendCurrencyMock
            ->expects($this->any())
            ->method('getColumn')
            ->willReturn($this->gridColumnMock);
        $this->gridColumnMock
            ->expects($this->any())
            ->method('getIndex')
            ->willReturn($columnIndex);
        $this->currencyMock
            ->expects($this->any())
            ->method('getRate')
            ->willReturn($rate);
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->currencyLocatorMock
            ->expects($this->any())
            ->method('getDefaultCurrency')
            ->willReturn($storeCurrencyCode);
        $currLocaleMock = $this->createMock(CurrencyData::class);
        $currLocaleMock
            ->expects($this->any())
            ->method('toCurrency')
            ->willReturn($convertedAmount);
        $this->localeCurrencyMock
            ->expects($this->any())
            ->method('getCurrency')
            ->with($storeCurrencyCode)
            ->willReturn($currLocaleMock);
        $this->gridColumnMock->method('getCurrency')->willReturn($storeCurrencyCode);
        $this->gridColumnMock->method('getRateField')->willReturn('test_rate_field');

        $actualAmount = $this->model->render($this->row);
        $this->assertEquals($convertedAmount, $actualAmount);
    }

    /**
     * DataProvider for testRender.
     *
     * @return array
     */
    public static function getCurrencyDataProvider(): array
    {
        return [
            'rate conversion with storefront' => [
                'rate' => 1.367,
                'columnIndex' => 'total_income_amount',
                'storeCurrencyCode' => 'EUR',
                'adminOrderAmount' => '100.00',
                'convertedAmount' => '€136.70',
            ],
        ];
    }

    protected function tearDown(): void
    {
        unset($this->storeManagerMock);
        unset($this->currencyLocatorMock);
        unset($this->localeCurrencyMock);
        unset($this->storeMock);
        unset($this->currencyMock);
        unset($this->backendCurrencyMock);
        unset($this->gridColumnMock);
    }
}
