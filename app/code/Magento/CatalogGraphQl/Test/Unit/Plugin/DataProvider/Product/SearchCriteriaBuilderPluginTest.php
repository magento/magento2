<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Plugin\DataProvider\Product;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Plugin\DataProvider\Product\SearchCriteriaBuilderPlugin;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config as TaxConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *  Unit Test for plugin Search Criteria Builder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchCriteriaBuilderPluginTest extends TestCase
{
    /** @var TaxConfig&MockObject */
    private TaxConfig $taxConfig;

    /** @var Calculation&MockObject */
    private Calculation $taxCalculation;

    /** @var GroupManagementInterface&MockObject */
    private GroupManagementInterface $groupManagement;

    /** @var PriceCurrencyInterface&MockObject */
    private PriceCurrencyInterface $priceCurrency;

    /** @var StoreManagerInterface&MockObject */
    private StoreManagerInterface $storeManager;

    /** @var ScopeConfigInterface&MockObject */
    private ScopeConfigInterface $scopeConfig;

    /** @var SearchCriteriaBuilder&MockObject */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var SearchCriteriaBuilderPlugin
     */
    private SearchCriteriaBuilderPlugin $plugin;

    protected function setUp(): void
    {
        $this->taxConfig = $this->createMock(TaxConfig::class);
        $this->taxCalculation = $this->createMock(Calculation::class);
        $this->groupManagement = $this->createMock(GroupManagementInterface::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $this->storeManager->method('getStore')->willReturn($store);

        $customerGroup = $this->createMock(GroupInterface::class);
        $customerGroup->method('getTaxClassId')->willReturn(3);
        $this->groupManagement->method('getNotLoggedInGroup')->willReturn($customerGroup);

        $this->plugin = new SearchCriteriaBuilderPlugin(
            $this->taxConfig,
            $this->taxCalculation,
            $this->groupManagement,
            $this->priceCurrency,
            $this->storeManager,
            $this->scopeConfig
        );
    }

    public function testBeforeBuildWithoutPriceFilter(): void
    {
        $args = ['filter' => ['sku' => ['eq' => 'test']], 'currentPage' => 1, 'pageSize' => 20];

        [$resultArgs, $includeAggregation] = $this->plugin->beforeBuild(
            $this->searchCriteriaBuilder,
            $args,
            false
        );

        self::assertSame($args, $resultArgs);
        self::assertFalse($includeAggregation);
    }

    public function testBeforeBuildWhenPriceConversionIsNotNeeded(): void
    {
        $args = [
            'filter' => ['price' => ['from' => 10, 'to' => 15]],
            'currentPage' => 1,
            'pageSize' => 20,
        ];

        $this->taxConfig->method('needPriceConversion')->with(1)->willReturn(false);

        [$resultArgs] = $this->plugin->beforeBuild($this->searchCriteriaBuilder, $args, false);

        self::assertSame(['from' => 10, 'to' => 15], $resultArgs['filter']['price']);
    }

    public function testBeforeBuildWhenTaxRateIsZero(): void
    {
        $args = [
            'filter' => ['price' => ['from' => 10, 'to' => 15]],
            'currentPage' => 1,
            'pageSize' => 20,
        ];

        $this->taxConfig->method('needPriceConversion')->with(1)->willReturn(TaxConfig::PRICE_CONVERSION_PLUS);
        $this->scopeConfig->method('getValue')
            ->with(TaxHelper::CONFIG_DEFAULT_PRODUCT_TAX_CLASS, ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(2);

        $rateRequest = new DataObject();
        $this->taxCalculation->method('getRateRequest')->willReturn($rateRequest);
        $this->taxCalculation->method('getRate')->willReturn(0.0);

        [$resultArgs] = $this->plugin->beforeBuild($this->searchCriteriaBuilder, $args, false);

        self::assertSame(['from' => 10, 'to' => 15], $resultArgs['filter']['price']);
    }

    /**
     * @param array $input
     * @param array $expected
     */
    #[DataProvider('priceConversionPlusDataProvider')]
    public function testBeforeBuildConvertsDisplayPriceToIndexedPrice(array $input, array $expected): void
    {
        $args = [
            'filter' => ['price' => $input],
            'currentPage' => 1,
            'pageSize' => 20,
        ];

        $this->taxConfig->method('needPriceConversion')->with(1)->willReturn(TaxConfig::PRICE_CONVERSION_PLUS);
        $this->scopeConfig->method('getValue')
            ->with(TaxHelper::CONFIG_DEFAULT_PRODUCT_TAX_CLASS, ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(2);

        $rateRequest = new DataObject();
        $this->taxCalculation->method('getRateRequest')->willReturn($rateRequest);
        $this->taxCalculation->method('getRate')->willReturn(7.5);
        $this->priceCurrency->method('round')->willReturnCallback(
            static fn (float $value): float => round($value, 2)
        );

        [$resultArgs] = $this->plugin->beforeBuild($this->searchCriteriaBuilder, $args, false);

        self::assertSame($expected, $resultArgs['filter']['price']);
    }

    public static function priceConversionPlusDataProvider(): array
    {
        return [
            'from_and_to' => [
                ['from' => 10, 'to' => 15],
                ['from' => 9.3, 'to' => 13.96],
            ],
            'from_only' => [
                ['from' => 10],
                ['from' => 9.3],
            ],
            'to_only' => [
                ['to' => 15],
                ['to' => 13.96],
            ],
        ];
    }
}
