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
}
