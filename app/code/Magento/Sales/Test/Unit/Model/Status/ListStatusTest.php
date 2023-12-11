<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Status;

use Magento\CatalogInventory\Helper\Data;
use Magento\Sales\Model\Status\ListStatus;

use PHPUnit\Framework\TestCase;

class ListStatusTest extends TestCase
{
    /** @var ListStatus */
    private $listStatus;

    protected function setUp(): void
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
        $this->assertInstanceOf(ListStatus::class, $result);
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
        $this->assertInstanceOf(ListStatus::class, $result);
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
