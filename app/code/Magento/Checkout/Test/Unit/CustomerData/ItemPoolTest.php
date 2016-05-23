<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\CustomerData;

class ItemPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var string
     */
    protected $defaultItemId = 'default_item_id';

    /**
     * @var string[]
     */
    protected $itemMap = [];

    /**
     * @var \Magento\Checkout\CustomerData\ItemPool
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->objectManagerMock = $this->getMock('\Magento\Framework\ObjectManagerInterface');
        $this->model = $objectManager->getObject(
            'Magento\Checkout\CustomerData\ItemPool',
            [
                'objectManager' => $this->objectManagerMock,
                'defaultItemId' => $this->defaultItemId,
                'itemMap' => $this->itemMap,
            ]
        );
    }

    public function testGetItemDataIfItemNotExistInMap()
    {
        $itemData = ['key' => 'value'];
        $productType = 'product_type';
        $quoteItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $quoteItemMock->expects($this->once())->method('getProductType')->willReturn($productType);

        $itemMock = $this->getMock('\Magento\Checkout\CustomerData\ItemInterface');
        $itemMock->expects($this->once())->method('getItemData')->with($quoteItemMock)->willReturn($itemData);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->defaultItemId)
            ->willReturn($itemMock);

        $this->assertEquals($itemData, $this->model->getItemData($quoteItemMock));
    }

    public function testGetItemDataIfItemExistInMap()
    {
        $itemData = ['key' => 'value'];
        $productType = 'product_type';
        $this->itemMap[$productType] = 'product_id';

        $quoteItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $quoteItemMock->expects($this->once())->method('getProductType')->willReturn($productType);

        $itemMock = $this->getMock('\Magento\Checkout\CustomerData\ItemInterface');
        $itemMock->expects($this->once())->method('getItemData')->with($quoteItemMock)->willReturn($itemData);

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->itemMap[$productType])
            ->willReturn($itemMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Checkout\CustomerData\ItemPool',
            [
                'objectManager' => $this->objectManagerMock,
                'defaultItemId' => $this->defaultItemId,
                'itemMap' => $this->itemMap,
            ]
        );

        $this->assertEquals($itemData, $this->model->getItemData($quoteItemMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * #@expectedExceptionMessage product_type doesn't extend \Magento\Checkout\CustomerData\ItemInterface
     */
    public function testGetItemDataIfItemNotValid()
    {
        $itemData = ['key' => 'value'];
        $productType = 'product_type';
        $quoteItemMock = $this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false);
        $quoteItemMock->expects($this->once())->method('getProductType')->willReturn($productType);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->defaultItemId)
            ->willReturn($this->getMock('\Magento\Quote\Model\Quote\Item', [], [], '', false));
        $this->assertEquals($itemData, $this->model->getItemData($quoteItemMock));
    }
}
