<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Plugin\ProductLinks;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductLinksTest extends TestCase
{
    /**
     * @var ProductLinks
     */
    protected $model;

    /**
     * @var Configuration|MockObject
     */
    protected $configMock;

    /**
     * @var Stock|MockObject
     */
    protected $stockHelperMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Configuration::class);
        $this->stockHelperMock = $this->createMock(Stock::class);

        $this->model = new ProductLinks(
            $this->configMock,
            $this->stockHelperMock
        );
    }

    /**
     * @dataProvider stockStatusDataProvider
     */
    public function testAfterGetProductCollectionShow($status, $callCount)
    {
        list($collectionMock, $subjectMock) = $this->buildMocks();
        $this->configMock->expects($this->once())->method('isShowOutOfStock')->willReturn($status);
        $this->stockHelperMock
            ->expects($this->exactly($callCount))
            ->method('addIsInStockFilterToCollection')
            ->with($collectionMock);

        $this->assertEquals($collectionMock, $this->model->afterGetProductCollection($subjectMock, $collectionMock));
    }

    /**
     * @return array
     */
    private function buildMocks()
    {
        /** @var Collection $collectionMock */
        $collectionMock = $this->createMock(
            Collection::class
        );

        /** @var Link $subjectMock */
        $subjectMock = $this->createMock(Link::class);
        return [$collectionMock, $subjectMock];
    }

    /**
     * @return array
     */
    public static function stockStatusDataProvider()
    {
        return [
            [0, 1],
            [1, 0],
        ];
    }
}
