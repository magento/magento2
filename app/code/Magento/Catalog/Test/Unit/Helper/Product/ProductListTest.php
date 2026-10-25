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

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->productListHelper = $objectManager->getObject(ProductList::class, [
            'scopeConfig' => $this->scopeConfigMock
        ]);
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
