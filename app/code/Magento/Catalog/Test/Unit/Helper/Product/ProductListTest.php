<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductListTest extends TestCase
{
    private const STUB_VIEW_MODE = 'grid';
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ProductList
     */
    private $productListHelper;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->productListHelper = $objectManager->getObject(ProductList::class, [
            'scopeConfig' => $this->scopeConfigMock,
            'storeManager' => $this->storeManagerMock
        ]);
    }

    #[DataProvider('scopeDataProvider')]
    public function testConfigIsReadFromScopeMatchingStoreMode(bool $singleStoreMode, string $expectedScope): void
    {
        $this->storeManagerMock->method('isSingleStoreMode')->willReturn($singleStoreMode);
        $this->scopeConfigMock->method('getValue')->willReturnCallback(
            fn(string $path, ?string $scope = null) => match ($path) {
                ProductList::XML_PATH_LIST_MODE => 'grid',
                'catalog/frontend/grid_per_page_values' => $scope === $expectedScope ? '12,24' : '6,9',
                'catalog/frontend/grid_per_page' => $scope === $expectedScope ? 24 : 9,
                default => null
            }
        );
        $this->scopeConfigMock->method('isSetFlag')
            ->willReturnCallback(fn(string $path, ?string $scope = null) => $scope === $expectedScope);

        $this->assertSame(['12' => '12', '24' => '24'], array_diff_key(
            $this->productListHelper->getAvailableLimit('grid'),
            ['all' => true]
        ));
        $this->assertArrayHasKey('all', $this->productListHelper->getAvailableLimit('grid'));
        $this->assertSame(24, $this->productListHelper->getDefaultLimitPerPageValue('grid'));
    }

    public static function scopeDataProvider(): array
    {
        return [
            'single store mode reads default scope' => [true, ScopeConfigInterface::SCOPE_TYPE_DEFAULT],
            'multi store reads store scope' => [false, ScopeInterface::SCOPE_STORE]
        ];
    }

    #[DataProvider('defaultAvailableLimitsDataProvider')]
    public function testGetDefaultLimitPerPageValueReturnsOneOfAvailableLimits(
        string $availableValues,
        int $defaultValue,
        int $expectedReturn
    ) {
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [sprintf('catalog/frontend/%s_per_page_values', self::STUB_VIEW_MODE), $availableValues],
                [sprintf('catalog/frontend/%s_per_page', self::STUB_VIEW_MODE), $defaultValue]
            ]);

        $returnedValue = $this->productListHelper->getDefaultLimitPerPageValue(self::STUB_VIEW_MODE);

        $this->assertSame($expectedReturn, $returnedValue);
    }

    public static function defaultAvailableLimitsDataProvider(): array
    {
        return [
            'limit-available' => [
                'availableValues' => '10,20,30',
                'defaultValue' => 10,
                'expectedReturn' => 10
            ],
            'limit-not-available' => [
                'availableValues' => '10,20,30',
                'defaultValue' => 1,
                'expectedReturn' => 10
            ]
        ];
    }
}
