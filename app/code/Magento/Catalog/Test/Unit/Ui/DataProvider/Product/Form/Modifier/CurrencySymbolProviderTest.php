<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CurrencySymbolProvider;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Currency\Data\Currency as CurrencyData;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Website Currency Symbol provider
 */
class CurrencySymbolProviderTest extends TestCase
{
    /**
     * @var CurrencySymbolProvider|MockObject
     */
    private $currencySymbolProvider;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var LocatorInterface|MockObject
     */
    private $locatorMock;

    /**
     * @var CurrencyInterface|MockObject
     */
    private $localeCurrencyMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $currentStoreMock;

    /**
     * @var CurrencyModel|MockObject
     */
    private $currencyMock;

    /**
     * @var CurrencyData|MockObject
     */
    private $websiteCurrencyMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

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
            ['getWebsites']
        );
        $this->currentStoreMock = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getBaseCurrency']
        );
        $this->currencyMock = $this->createMock(CurrencyModel::class);
        $this->websiteCurrencyMock = $this->createMock(CurrencyData::class);
        $this->productMock = $this->createMock(Product::class);
        $this->locatorMock = $this->getMockForAbstractClass(
            LocatorInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getStore', 'getProduct']
        );
        $this->localeCurrencyMock = $this->getMockForAbstractClass(
            CurrencyInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['getWebsites', 'getCurrency']
        );
        $this->currencySymbolProvider = $objectManager->getObject(
            CurrencySymbolProvider::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'locator' => $this->locatorMock,
                'localeCurrency' => $this->localeCurrencyMock
            ]
        );
    }

    /**
     * Test for Get option array of currency symbol prefixes.
     *
     * @param int $catalogPriceScope
     * @param string $defaultStoreCurrencySymbol
     * @param array $listOfWebsites
     * @param array $productWebsiteIds
     * @param array $currencySymbols
     * @param array $actualResult
     * @dataProvider getWebsiteCurrencySymbolDataProvider
     */
    public function testGetCurrenciesPerWebsite(
        int $catalogPriceScope,
        string $defaultStoreCurrencySymbol,
        array $listOfWebsites,
        array $productWebsiteIds,
        array $currencySymbols,
        array $actualResult
    ): void {
        $this->locatorMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->currentStoreMock);
        $this->currentStoreMock->expects($this->any())
            ->method('getBaseCurrency')
            ->willReturn($this->currencyMock);
        $this->currencyMock->expects($this->any())
            ->method('getCurrencySymbol')
            ->willReturn($defaultStoreCurrencySymbol);
        $this->scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturn($catalogPriceScope);
        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->storeManagerMock->expects($this->any())
            ->method('getWebsites')
            ->willReturn($listOfWebsites);
        $this->productMock->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn($productWebsiteIds);
        $this->localeCurrencyMock->expects($this->any())
            ->method('getCurrency')
            ->willReturn($this->websiteCurrencyMock);
        foreach ($currencySymbols as $currencySymbol) {
            $this->websiteCurrencyMock->expects($this->any())
                ->method('getSymbol')
                ->willReturn($currencySymbol);
        }
        $expectedResult = $this->currencySymbolProvider
            ->getCurrenciesPerWebsite();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * DataProvider for getCurrenciesPerWebsite.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getWebsiteCurrencySymbolDataProvider(): array
    {
        return [
            'verify website currency with default website and global price scope' => [
                'catalogPriceScope' => 0,
                'defaultStoreCurrencySymbol' => '$',
                'listOfWebsites' => $this->getWebsitesMock(
                    [
                        [
                            'id' => '1',
                            'name' => 'Main Website',
                            'code' => 'main_website',
                            'base_currency_code' => 'USD',
                            'currency_symbol' => '$'
                        ]
                    ]
                ),
                'productWebsiteIds' => ['1'],
                'currencySymbols' => ['$'],
                'actualResult' => ['$']
            ],
            'verify website currency with default website and website price scope' => [
                'catalogPriceScope' => 1,
                'defaultStoreCurrencySymbol' => '$',
                'listOfWebsites' => $this->getWebsitesMock(
                    [
                        [
                            'id' => '1',
                            'name' => 'Main Website',
                            'code' => 'main_website',
                            'base_currency_code' => 'USD',
                            'currency_symbol' => '$'
                        ]
                    ]
                ),
                'productWebsiteIds' => ['1'],
                'currencySymbols' => ['$'],
                'actualResult' => ['$', '$']
            ],
            'verify website currency with two website and website price scope' => [
                'catalogPriceScope' => 1,
                'defaultStoreCurrencySymbol' => '$',
                'listOfWebsites' => $this->getWebsitesMock(
                    [
                        [
                            'id' => '1',
                            'name' => 'Main Website',
                            'code' => 'main_website',
                            'base_currency_code' => 'USD',
                            'currency_symbol' => '$'
                        ],
                        [
                            'id' => '2',
                            'name' => 'Indian Website',
                            'code' => 'indian_website',
                            'base_currency_code' => 'INR',
                            'currency_symbol' => '₹'
                        ]
                    ]
                ),
                'productWebsiteIds' => ['1', '2'],
                'currencySymbols' => ['$', '₹'],
                'actualResult' => ['$', '$', '$']
            ]
        ];
    }

    /**
     * Get list of websites mock
     *
     * @param array $websites
     * @return array
     */
    private function getWebsitesMock(array $websites): array
    {
        $websitesMock = [];
        foreach ($websites as $key => $website) {
            $websitesMock[$key] = $this->getMockForAbstractClass(
                WebsiteInterface::class,
                [],
                '',
                true,
                true,
                true,
                ['getId', 'getBaseCurrencyCode']
            );
            $websitesMock[$key]->expects($this->any())
                ->method('getId')
                ->willReturn($website['id']);
            $websitesMock[$key]->expects($this->any())
                ->method('getBaseCurrencyCode')
                ->willReturn($website['base_currency_code']);
        }
        return $websitesMock;
    }

    protected function tearDown(): void
    {
        unset($this->scopeConfigMock);
        unset($this->storeManagerMock);
        unset($this->currentStoreMock);
        unset($this->currencyMock);
        unset($this->websiteCurrencyMock);
        unset($this->productMock);
        unset($this->locatorMock);
        unset($this->localeCurrencyMock);
    }
}
