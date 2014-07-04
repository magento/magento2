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
     * @var \Magento\CatalogInventory\Service\V1\StockItemService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemService;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->stockItemService = $this->getMock(
            'Magento\CatalogInventory\Service\V1\StockItemService',
            [],
            [],
            '',
            false
        );

        $this->block = $objectManager->getObject(
            'Magento\CatalogInventory\Block\Stockqty\DefaultStockqty',
            array('registry' => $this->registryMock, 'stockItemService' => $this->stockItemService)
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTags = array('catalog_product_1');
        $product = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
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
     * @param int|null $dataQty
     * @param int $expectedQty
     * @dataProvider getStockQtyDataProvider
     */
    public function testGetStockQty($productStockQty, $productId, $dataQty, $expectedQty)
    {
        $this->assertNull($this->block->getData('product_stock_qty'));
        if ($dataQty) {
            $this->setDataArrayValue('product_stock_qty', $dataQty);
        } else {
            $product = $this->getMock('Magento\Catalog\Model\Product', ['getId', '__wakeup'], [], '', false);
            $product->expects($this->any())->method('getId')->will($this->returnValue($productId));

            $this->registryMock->expects($this->any())
                ->method('registry')
                ->with('current_product')
                ->will($this->returnValue($product));

            if ($productId) {
                $this->stockItemService->expects($this->once())
                    ->method('getStockQty')
                    ->with($this->equalTo($productId))
                    ->will($this->returnValue($productStockQty));
            }
        }
        $this->assertSame($expectedQty, $this->block->getStockQty());
        $this->assertSame($expectedQty, $this->block->getData('product_stock_qty'));
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
                'default qty' => null,
                'expected qty' => 100
            ],
            [
                'product qty' => 100,
                'product id' => null,
                'default qty' => null,
                'expected qty' => 0
            ],
            [
                'product qty' => null,
                'product id' => null,
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
