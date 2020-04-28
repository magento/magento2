<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Category\Toolbar\Config as ToolbarConfig;

class ProductListTest extends TestCase
{
    const STUB_VIEW_MODE = 'grid';

    /**
     * @var ProductList
     */
    private $object;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ToolbarConfig|MockObject
     */
    private $toolbarConfigMock;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->toolbarConfigMock = $this->createMock(ToolbarConfig::class);

        $this->object = new ProductList(
            $this->scopeConfigMock,
            $this->toolbarConfigMock
        );
    }

    public function testGetDefaultSortField(): void
    {
        $order = 'position';

        $this->toolbarConfigMock->expects($this->any())
            ->method('getOrderField')
            ->willReturn($order);
        $this->assertEquals($order, $this->object->getDefaultSortField());
    }

    /**
     * @dataProvider defaultAvailableLimitsDataProvider
     */
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

        $returnedValue = $this->object->getDefaultLimitPerPageValue(self::STUB_VIEW_MODE);

        $this->assertSame($expectedReturn, $returnedValue);
    }

    public function defaultAvailableLimitsDataProvider(): array
    {
        return [
            'limit-available' => [
                'values' => '10,20,30',
                'default' => 10,
                'expected' => 10
            ],
            'limit-not-available' => [
                'values' => '10,20,30',
                'default' => 1,
                'expected' => 10
            ]
        ];
    }
}
