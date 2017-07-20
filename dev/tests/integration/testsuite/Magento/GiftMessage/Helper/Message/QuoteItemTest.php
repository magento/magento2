<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Helper\Message;

/**
 * Class QuoteItemTest
 */
class QuoteItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem
     */
    private $toOrderItem;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->toOrderItem = $this->objectManager->create(
            \Magento\Quote\Model\Quote\Item\ToOrderItem::class
        );
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDataFixture Magento/GiftMessage/_files/simple_quote_using_product.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 0
     * @return void
     */
    public function testMessageAvailableMatchesDefaultFalse()
    {
        /** @var $quoteFixture \Magento\Quote\Model\Quote */
        $quoteFixture = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');

        $quoteItem = current($quoteFixture->getAllItems());

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->toOrderItem->convert($quoteItem);

        $this->assertEquals(0, $orderItem->getGiftMessageAvailable());
    }

    /**
     * @magentoDataFixture Magento/Bundle/_files/product.php
     * @magentoDataFixture Magento/GiftMessage/_files/simple_quote_using_product.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @return void
     */
    public function testMessageAvailableMatchesDefaultTrue()
    {
        /** @var $quoteFixture \Magento\Quote\Model\Quote */
        $quoteFixture = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');

        $quoteItem = current($quoteFixture->getAllItems());

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->toOrderItem->convert($quoteItem);

        $this->assertEquals(1, $orderItem->getGiftMessageAvailable());
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/product_with_message_available.php
     * @magentoDataFixture Magento/GiftMessage/_files/simple_quote_using_product.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 0
     * @return void
     */
    public function testMessageAvailable()
    {
        /** @var $quoteFixture \Magento\Quote\Model\Quote */
        $quoteFixture = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');

        $quoteItem = current($quoteFixture->getAllItems());

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->toOrderItem->convert($quoteItem);

        $this->assertEquals(1, $orderItem->getGiftMessageAvailable());
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/product_with_message_not_available.php
     * @magentoDataFixture Magento/GiftMessage/_files/simple_quote_using_product.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @return void
     */
    public function testMessageNotAvailable()
    {
        /** @var $quoteFixture \Magento\Quote\Model\Quote */
        $quoteFixture = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');

        $quoteItem = current($quoteFixture->getAllItems());

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->toOrderItem->convert($quoteItem);

        $this->assertEquals(0, $orderItem->getGiftMessageAvailable());
    }

}