<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Service\V1\Item;

use Magento\Checkout\Service\V1\Data\Cart\Item as Item;

class ReaderServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMapperMock;

    protected function setUp()
    {
        $this->quoteRepositoryMock = $this->getMock('Magento\Sales\Model\QuoteRepository', [], [], '', false);
        $this->itemMapperMock =
            $this->getMock('\Magento\Checkout\Service\V1\Data\Cart\ItemMapper', ['extractDto'], [], '', false);
        $this->service = new ReadService($this->quoteRepositoryMock, $this->itemMapperMock);
    }

    public function testGetList()
    {
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with(33)
            ->will($this->returnValue($quoteMock));
        $itemMock = $this->getMock('\Magento\Sales\Model\Quote\Item',
            ['getSku', 'getName', 'getPrice', 'getQty', 'getProductType', '__wakeup'], [], '', false);
        $quoteMock->expects($this->any())->method('getAllItems')->will($this->returnValue([$itemMock]));
        $testData = [
            Item::ITEM_ID => 7,
            Item::SKU => 'prd_SKU',
            Item::NAME => 'prd_NAME',
            Item::PRICE => 100.15,
            Item::QTY => 16,
            Item::PRODUCT_TYPE => 'simple',
        ];

        $this->itemMapperMock
            ->expects($this->once())
            ->method('extractDto')
            ->with($itemMock)
            ->will($this->returnValue($testData));
        $this->assertEquals([$testData], $this->service->getList(33));
    }
}
