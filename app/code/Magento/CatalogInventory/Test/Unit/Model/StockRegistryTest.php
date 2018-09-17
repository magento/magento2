<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model;

/**
 * Class StockRegistryTest
 */
class StockRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteria;

    protected function setUp()
    {
        $this->criteria = $this->getMockBuilder('Magento\CatalogInventory\Api\StockItemCriteriaInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $criteriaFactory = $this->getMockBuilder('Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $criteriaFactory->expects($this->once())->method('create')->willReturn($this->criteria);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\CatalogInventory\Model\StockRegistry',
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
