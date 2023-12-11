<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for QuoteItemQtyList class
 */
class QuoteItemQtyListTest extends TestCase
{
    /**
     * @var QuoteItemQtyList
     */
    private $quoteItemQtyList;

    /**
     * @var int
     */
    private $itemQtyTestValue;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->quoteItemQtyList = $objectManagerHelper->getObject(QuoteItemQtyList::class);
    }

    /**
     * This tests the scenario when item has not quote_item_id and after save gets a value.
     *
     * @return void
     */
    public function testSingleQuoteItemQty()
    {
        $this->itemQtyTestValue = 1;
        $qty = $this->quoteItemQtyList->getQty(125, null, 11232, 1);
        $this->assertEquals($this->itemQtyTestValue, $qty);

        $qty = $this->quoteItemQtyList->getQty(125, 1, 11232, 1);
        $this->assertEquals($this->itemQtyTestValue, $qty);

        $this->itemQtyTestValue = 2;
        $qty = $this->quoteItemQtyList->getQty(125, null, 11232, 1);
        $this->assertNotEquals($this->itemQtyTestValue, $qty);
    }

    /**
     * This tests the scenario when item has been added twice to the cart.
     *
     * @return void
     */
    public function testMultipleQuoteItemQty()
    {
        $this->itemQtyTestValue = 1;
        $qty = $this->quoteItemQtyList->getQty(127, 1, 112, 1);
        $this->assertEquals($this->itemQtyTestValue, $qty);

        $this->itemQtyTestValue = 2;
        $qty = $this->quoteItemQtyList->getQty(127, 2, 112, 1);
        $this->assertEquals($this->itemQtyTestValue, $qty);
    }
}
