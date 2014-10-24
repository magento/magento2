<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\CatalogInventory\Model\Stock\Item;

/**
 * Class ItemRegistryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ItemRegistry
     */
    protected $model;

    /** @var Item|\PHPUnit_Framework_MockObject_MockObject */

    protected $stockItemRegistry;

    /** @var \Magento\CatalogInventory\Model\Stock\ItemFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemFactory;

    /** @var \Magento\CatalogInventory\Model\Resource\Stock\Item| \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemResource;

    protected function setUp()
    {
        $this->stockItemFactory = $this
            ->getMockBuilder('Magento\CatalogInventory\Model\Stock\ItemFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemResource = $this
            ->getMockBuilder('Magento\CatalogInventory\Model\Resource\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->model = $objectManagerHelper->getObject(
            'Magento\CatalogInventory\Model\Stock\ItemRegistry',
            [
                'stockItemFactory' => $this->stockItemFactory,
                'stockItemResource' => $this->stockItemResource
            ]
        );
    }

    public function testRetrieve()
    {
        $productId = 3;
        $times = 1;

        $stockItem = $this->buildStockItem($productId, $times);

        $this->assertEquals($stockItem, $this->model->retrieve($productId));
        $this->assertEquals($stockItem, $this->model->retrieve($productId));
    }

    public function testErase()
    {
        $productId = 3;
        $times = 2;

        $stockItem = $this->buildStockItem($productId, $times);

        $this->model->retrieve($productId);
        $this->assertEquals($this->model, $this->model->erase($productId));
        $this->assertEquals($stockItem, $this->model->retrieve($productId));
    }

    /**
     * @param $productId
     * @param $times
     * @return \PHPUnit_Framework_MockObject_MockObject|Item
     */
    private function buildStockItem($productId, $times)
    {
        $stockItem = $this->stockItemRegistry = $this
            ->getMockBuilder('Magento\CatalogInventory\Model\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemFactory
            ->expects($this->exactly($times))
            ->method('create')
            ->will($this->returnValue($stockItem));
        $this->stockItemResource
            ->expects($this->exactly($times))
            ->method('loadByProductId')
            ->with($stockItem, $productId)
            ->will($this->returnSelf());

        return $stockItem;
    }
}
