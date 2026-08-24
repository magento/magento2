<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ProductList
     */
    private $productListHelper;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->productListHelper = $objectManager->getObject(ProductList::class, [
            'scopeConfig' => $this->scopeConfigMock,
            'categoryRepository' => $this->categoryRepositoryMock,
            'request' => $this->requestMock,
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

    public function testGetDefaultSortFieldReturnsCategorySortBy(): void
    {
        $categoryMock = $this->createMock(Category::class);
        $categoryMock->method('getDefaultSortBy')->willReturn('name');

        $this->requestMock->method('getParam')->with('id')->willReturn('5');
        $this->categoryRepositoryMock->method('get')->with(5)->willReturn($categoryMock);

        $this->assertSame('name', $this->productListHelper->getDefaultSortField());
    }

    public function testGetDefaultSortFieldFallsBackToConfigOnMissingCategory(): void
    {
        $this->requestMock->method('getParam')->with('id')->willReturn('5');
        $this->categoryRepositoryMock->method('get')->with(5)
            ->willThrowException(new NoSuchEntityException());
        $this->scopeConfigMock->method('getValue')->willReturn('price');

        $this->assertSame('price', $this->productListHelper->getDefaultSortField());
    }

    public function testGetDefaultSortFieldFallsBackToConfigWithNoCategoryId(): void
    {
        $this->requestMock->method('getParam')->with('id')->willReturn('0');
        $this->scopeConfigMock->method('getValue')->willReturn('price');

        $this->assertSame('price', $this->productListHelper->getDefaultSortField());
    }
}
