<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model;

use Magento\CatalogInventory\Model\AddStockStatusToCollection;

class AddStockStatusToCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddStockStatusToCollection
     */
    protected $plugin;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockHelper;

    protected function setUp()
    {
        $this->stockHelper = $this->getMock('Magento\CatalogInventory\Helper\Stock', [], [], '', false);
        $this->plugin = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\CatalogInventory\Model\AddStockStatusToCollection',
            [
                'stockHelper' => $this->stockHelper,
            ]
        );
    }

    public function testAddStockStatusToCollection()
    {
        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockHelper->expects($this->once())
            ->method('addIsInStockFilterToCollection')
            ->with($productCollection)
            ->will($this->returnSelf());

        $this->plugin->beforeLoad($productCollection);
    }
}
