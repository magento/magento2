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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class Currency.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var WebsiteInterface|MockObject
     */
    private $websiteMock;

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

        $this->storeMock = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getWebsiteId', 'getCurrentCurrencyCode']
        );

        $this->websiteMock = $this->getMockForAbstractClass(
            WebsiteInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getBaseCurrencyCode']
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
     * @param int $catalogPriceScope
     * @param int $adminWebsiteId
     * @param string $adminCurrencyCode
     * @param string $storeCurrencyCode
     * @param string $adminOrderAmount
     * @param string $convertedAmount
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws CurrencyException
     * @dataProvider getCurrencyDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRender(
        float $rate,
        string $columnIndex,
        int $catalogPriceScope,
        int $adminWebsiteId,
        string $adminCurrencyCode,
        string $storeCurrencyCode,
        string $adminOrderAmount,
        string $convertedAmount
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
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn($catalogPriceScope);
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock
            ->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($adminWebsiteId);
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getWebsite')
            ->with($adminWebsiteId)
            ->willReturn($this->websiteMock);
        $this->websiteMock
            ->expects($this->any())
            ->method('getBaseCurrencyCode')
            ->willReturn($adminCurrencyCode);
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
        $this->gridColumnMock->method('getCurrency')->willReturn('USD');
        $this->gridColumnMock->method('getRateField')->willReturn('test_rate_field');
        $actualAmount = $this->model->render($this->row);
        $this->assertEquals($convertedAmount, $actualAmount);
    }

    /**
     * DataProvider for testRender.
     *
     * @return array
     */
    public function getCurrencyDataProvider(): array
    {
        return [
            'rate conversion with same admin and storefront rate' => [
                'rate' => 1.00,
                'columnIndex' => 'total_income_amount',
                'catalogPriceScope' => 1,
                'adminWebsiteId' => 1,
                'adminCurrencyCode' => 'EUR',
                'storeCurrencyCode' => 'EUR',
                'adminOrderAmount' => '105.00',
                'convertedAmount' => '105.00'
            ],
            'rate conversion with different admin and storefront rate' => [
                'rate' => 1.4150,
                'columnIndex' => 'total_income_amount',
                'catalogPriceScope' => 1,
                'adminWebsiteId' => 1,
                'adminCurrencyCode' => 'USD',
                'storeCurrencyCode' => 'EUR',
                'adminOrderAmount' => '105.00',
                'convertedAmount' => '148.575'
            ]
        ];
    }

    protected function tearDown(): void
    {
        unset($this->scopeConfigMock);
        unset($this->storeManagerMock);
        unset($this->currencyLocatorMock);
        unset($this->localeCurrencyMock);
        unset($this->websiteMock);
        unset($this->storeMock);
        unset($this->currencyMock);
        unset($this->backendCurrencyMock);
        unset($this->gridColumnMock);
    }
}
