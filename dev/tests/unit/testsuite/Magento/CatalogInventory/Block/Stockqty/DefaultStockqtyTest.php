<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Block\Stockqty;

/**
 * Unit test for DefaultStockqty
 */
class DefaultStockqtyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Block\Stockqty\DefaultStockqty
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->stockState = $this->getMock(
            'Magento\CatalogInventory\Api\StockStateInterface',
            [],
            [],
            '',
            false
        );
        $this->stockRegistryMock = $this->getMockBuilder('Magento\CatalogInventory\Api\StockRegistryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->block = $objectManager->getObject(
            'Magento\CatalogInventory\Block\Stockqty\DefaultStockqty',
            [
                'registry' => $this->registryMock,
                'stockState' => $this->stockState,
                'stockRegistry' => $this->stockRegistryMock
            ]
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->once())->method('getIdentities')->will($this->returnValue($productTags));
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));
        $this->assertEquals($productTags, $this->block->getIdentities());
    }

    /**
     * @param int $productStockQty
     * @param int|null $productId
     * @param int|null $websiteId
     * @param int|null $dataQty
     * @param int $expectedQty
     * @dataProvider getStockQtyDataProvider
     */
    public function testGetStockQty($productStockQty, $productId, $websiteId, $dataQty, $expectedQty)
    {
        $this->assertNull($this->block->getData('product_stock_qty'));
        if ($dataQty) {
            $this->setDataArrayValue('product_stock_qty', $dataQty);
        } else {
            $product = $this->getMock(
                'Magento\Catalog\Model\Product',
                ['getId', 'getStore', '__wakeup'],
                [],
                '',
                false
            );
            $product->expects($this->any())->method('getId')->will($this->returnValue($productId));
            $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId', '__wakeup'], [], '', false);
            $store->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
            $product->expects($this->any())->method('getStore')->will($this->returnValue($store));

            $this->registryMock->expects($this->any())
                ->method('registry')
                ->with('current_product')
                ->will($this->returnValue($product));

            if ($productId) {
                $this->stockState->expects($this->once())
                    ->method('getStockQty')
                    ->with($this->equalTo($productId), $this->equalTo($websiteId))
                    ->will($this->returnValue($productStockQty));
            }
        }
        $this->assertSame($expectedQty, $this->block->getStockQty());
        $this->assertSame($expectedQty, $this->block->getData('product_stock_qty'));
    }

    public function te1stGetStockQtyLeft()
    {
        $productId = 1;
        $minQty = 0;
        $websiteId = 1;
        $stockQty = 2;

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->any())
            ->method('getId')
            ->willReturn($productId);
        $product->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

        $stockItemMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $stockItemMock->expects($this->once())
            ->method('getMinQty')
            ->willReturn($minQty);
        $this->stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId)
            ->willReturn($stockItemMock);
        $this->stockState->expects($this->once())
            ->method('getStockQty')
            ->with($productId, $storeMock)
            ->willReturn($stockQty);

        $this->assertEquals($stockQty, $this->block->getStockQtyLeft());
    }

    /**
     * @return array
     */
    public function getStockQtyDataProvider()
    {
        return [
            [
                'product qty' => 100,
                'product id' => 5,
                'website id' => 0,
                'default qty' => null,
                'expected qty' => 100,
            ],
            [
                'product qty' => 100,
                'product id' => null,
                'website id' => null,
                'default qty' => null,
                'expected qty' => 0
            ],
            [
                'product qty' => null,
                'product id' => null,
                'website id' => null,
                'default qty' => 50,
                'expected qty' => 50
            ],
        ];
    }

    /**
     * @param string $key
     * @param string|float|int $value
     */
    protected function setDataArrayValue($key, $value)
    {
        $property = new \ReflectionProperty($this->block, '_data');
        $property->setAccessible(true);
        $dataArray = $property->getValue($this->block);
        $dataArray[$key] = $value;
        $property->setValue($this->block, $dataArray);
    }
}
