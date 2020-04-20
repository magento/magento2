<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Category\Toolbar\Config as ToolbarConfig;

class ProductListTest extends TestCase
{
    /**
     * @var ProductList
     */
    private $object;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var ToolbarConfig|MockObject
     */
    private $toolbarConfig;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->toolbarConfig = $this->createMock(ToolbarConfig::class);

        $this->object = new ProductList(
            $this->scopeConfig,
            $this->toolbarConfig
        );
    }

    public function testGetDefaultSortField(): void
    {
        $order = 'position';

        $this->toolbarConfig->expects($this->any())
            ->method('getOrderField')
            ->willReturn($order);
        $this->assertEquals($order, $this->object->getDefaultSortField());
    }
}
