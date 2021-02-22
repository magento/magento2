<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model;

/**
 * Class StockRegistryTest
 */
class StockRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $criteria;

    protected function setUp(): void
    {
        $this->criteria = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockItemCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $criteriaFactory = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $criteriaFactory->expects($this->once())->method('create')->willReturn($this->criteria);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\CatalogInventory\Model\StockRegistry::class,
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
