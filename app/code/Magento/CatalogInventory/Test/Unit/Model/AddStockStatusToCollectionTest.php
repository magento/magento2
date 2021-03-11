<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\CatalogInventory\Model\AddStockStatusToCollection;
use Magento\Framework\Search\EngineResolverInterface;

class AddStockStatusToCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AddStockStatusToCollection
     */
    protected $plugin;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockHelper;

    /**
     * @var EngineResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $engineResolver;

    protected function setUp(): void
    {
        $this->stockHelper = $this->createMock(\Magento\CatalogInventory\Helper\Stock::class);
        $this->engineResolver = $this->getMockBuilder(EngineResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentSearchEngine'])
            ->getMockForAbstractClass();

        $this->plugin = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\CatalogInventory\Model\AddStockStatusToCollection::class,
            [
                'stockHelper' => $this->stockHelper,
                'engineResolver' => $this->engineResolver
            ]
        );
    }

    public function testAddStockStatusToCollection()
    {
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->engineResolver->expects($this->any())
            ->method('getCurrentSearchEngine')
            ->willReturn('mysql');

        $this->stockHelper->expects($this->once())
            ->method('addIsInStockFilterToCollection')
            ->with($productCollection)
            ->willReturnSelf();

        $this->plugin->beforeLoad($productCollection);
    }
}
