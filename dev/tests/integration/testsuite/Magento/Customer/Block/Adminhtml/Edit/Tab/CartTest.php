<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Quote\Model\Quote;

/**
 * Class checks customer's shopping cart block with simple product and simple product with options.
 *
 * @see \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart
 * @magentoAppArea adminhtml
 */
class CartTest extends AbstractCartTest
{
    /**
     * @magentoDataFixture Magento/Checkout/_files/customer_quote_with_items_simple_product_options.php
     *
     * @return void
     */
    public function testProductOptionsView(): void
    {
        $this->processCheckQuoteItems('customer_uk_address@test.com');
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     * @return void
     */
    public function testCustomerWithoutQuoteView(): void
    {
        $this->processCheckWithoutQuoteItems('customer_two@example.com');
    }

    /**
     * Verify Grid with quote items
     *
     * @magentoDataFixture Magento/Sales/_files/quote_with_two_products_and_customer.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider getQuoteDataProvider
     *
     * @param int $customerId
     * @param bool $guest
     * @param bool $contains
     * @return void
     */
    public function testVerifyCollectionWithQuote(int $customerId, bool $guest, bool $contains): void
    {
        $session = $this->objectManager->create(SessionQuote::class);
        $session->setCustomerId($customerId);
        $quoteFixture = $this->objectManager->create(Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');
        $quoteFixture->setCustomerIsGuest($guest)
                     ->setCustomerId($customerId)
                     ->save();
        $this->block->toHtml();
        if ($contains) {
            $this->assertStringContainsString(
                "We couldn&#039;t find any records",
                $this->block->getGridParentHtml()
            );
        } else {
            $this->assertStringNotContainsString(
                "We couldn&#039;t find any records",
                $this->block->getGridParentHtml()
            );
        }
    }

    /**
     * Data provider for withQuoteTest
     *
     * @return array
     */
    public static function getQuoteDataProvider(): array
    {
        return [
            [
                 6,
                 false,
                 true
            ],
            [
                 self::CUSTOMER_ID_VALUE,
                 true,
                 false
            ],
        ];
    }

    /**
     * Verify Customer id
     *
     * @return void
     */
    public function testGetCustomerId(): void
    {
        $this->assertEquals(self::CUSTOMER_ID_VALUE, $this->block->getCustomerId());
    }

    /**
     * Verify get grid url
     *
     * @return void
     */
    public function testGetGridUrl(): void
    {
        $this->assertStringContainsString('/backend/customer/index/cart', $this->block->getGridUrl());
    }

    /**
     * Verify grid parent html
     *
     * @return void
     */
    public function testGetGridParentHtml(): void
    {
        $mockCollection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->block->setCollection($mockCollection);
        $this->assertStringContainsString(
            "<div class=\"admin__data-grid-header admin__data-grid-toolbar\"",
            $this->block->getGridParentHtml()
        );
    }

    /**
     * Verify row url
     *
     * @return void
     */
    public function testGetRowUrl(): void
    {
        $row = new \Magento\Framework\DataObject();
        $row->setProductId(1);
        $this->assertStringContainsString('/backend/catalog/product/edit/id/1', $this->block->getRowUrl($row));
    }

    /**
     * Verify get html
     *
     * @return void
     */
    public function testGetHtml(): void
    {
        $html = $this->block->toHtml();
        $this->assertStringContainsString("<div id=\"customer_cart_grid\"", $html);
        $this->assertStringContainsString("<div class=\"admin__data-grid-header admin__data-grid-toolbar\"", $html);
        $this->assertStringContainsString("customer_cart_gridJsObject = new varienGrid(\"customer_cart_grid\",", $html);
        $this->assertStringContainsString(
            'backend\u002Fcustomer\u002Fcart_product_composite_cart\u002Fconfigure\u002Fcustomer_id\u002F'
            . self::CUSTOMER_ID_VALUE,
            $html
        );
    }
}
