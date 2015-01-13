<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Status;

use Magento\CatalogInventory\Helper\Data;

class ListStatusTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Status\ListStatus */
    private $listStatus;

    protected function setUp()
    {
        $this->listStatus = new ListStatus();
    }

    public function testAddAndGetItem()
    {
        $origin = 'stock';
        $code = 'cataloginventory';
        $message = Data::ERROR_QTY;
        $additionalData = null;
        $mockItems = [
            [
                'origin' => $origin,
                'code' => $code,
                'message' => $message,
                'additionalData' => $additionalData,
            ],
        ];
        $result = $this->listStatus->addItem($origin, $code, $message, $additionalData);
        $items = $this->listStatus->getItems();
        $this->assertEquals($mockItems, $items);
        $this->assertInstanceOf('\Magento\Sales\Model\Status\ListStatus', $result);
    }

    public function testRemovePresentAndAbsentItems()
    {
        $presentAndAbsentIndex = [0, 1, 4];
        $mockItems = $this->addItems();
        $removedMockItems = $this->listStatus->removeItems($presentAndAbsentIndex);
        $this->assertEquals($mockItems, $removedMockItems);
    }

    public function testRemoveItemsByPresentAndAbsentParams()
    {
        $items = $this->addItems();
        $presentAndAbsentParams = ['message', 'noneparam'];
        $result = $this->listStatus->removeItemsByParams($presentAndAbsentParams);
        $this->assertEquals($items, $result);
    }

    public function testClear()
    {
        $this->addItems();
        $expected = [];
        $result = $this->listStatus->clear();
        $this->assertInstanceOf('\Magento\Sales\Model\Status\ListStatus', $result);
        $this->assertEquals($expected, $result->getItems());
    }

    /**
     * creates mock items and adds to listStatus
     *
     * @return array
     */
    protected function addItems()
    {
        $origin = 'stock';
        $code = 'cataloginventory';
        $message = Data::ERROR_QTY;
        $additionalData = null;
        $mockItems = [];

        for ($i = 0; $i < 2; $i++) {
            $mockItems[] = [
                'origin' => $origin . $i,
                'code' => $code,
                'message' => $message . $i,
                'additionalData' => $additionalData,
            ];
            $this->listStatus->addItem($origin . $i, $code, $message . $i, $additionalData);
        }
        return $mockItems;
    }
}
