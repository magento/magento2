<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockRegistryTest extends TestCase
{
    /**
     * @var StockRegistry
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $criteria;

    protected function setUp(): void
    {
        $this->criteria = $this->getMockBuilder(StockItemCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $criteriaFactory = $this->getMockBuilder(StockItemCriteriaInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $criteriaFactory->expects($this->once())->method('create')->willReturn($this->criteria);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            StockRegistry::class,
            [
                'criteriaFactory' => $criteriaFactory
            ]
        );
    }

    public function testGetLowStockItems()
    {
        $this->criteria->expects($this->once())->method('setLimit')->with(1, 0);
        $this->criteria->expects($this->once())->method('setScopeFilter')->with(1);
        $this->criteria->expects($this->once())->method('setQtyFilter')->with('<=');
        $this->criteria->expects($this->once())->method('addField')->with('qty');
        $this->model->getLowStockItems(1, 100);
    }
}
