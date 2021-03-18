<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Check sidebar shopping cart section block
 *
 * @see \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\Cart
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class CartTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var Cart */
    private $block;

    /** @var Quote */
    private $session;

    /** @var ProductRepository */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Cart::class);
        $this->session = $this->objectManager->get(Quote::class);
        $this->productRepository = $this->objectManager->get(ProductRepository::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->session->clearStorage();
        $this->objectManager->removeSharedInstance(\Magento\Sales\Model\AdminOrder\Create::class, true);
        $this->objectManager->removeSharedInstance(QuoteRepository::class);
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_customer_without_address.php
     *
     * @return void
     */
    public function testGetItemCollection(): void
    {
        $this->session->setCustomerId(1);
        $items = $this->block->getItemCollection();
        $this->assertCount(1, $items);
        $this->assertEquals('simple2', reset($items)->getSku());
    }

    /**
     * @return void
     */
    public function testClearShoppingCartButton(): void
    {
        $confirmation = __('Are you sure you want to delete all items from shopping cart?');
        $button = $this->block->getChildBlock('empty_customer_cart_button');
        $this->assertEquals(sprintf("order.clearShoppingCart('%s')", $confirmation), $button->getOnclick());
        $this->assertEquals(__('Clear Shopping Cart'), $button->getLabel());
    }

    /**
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoDataFixture Magento/Sales/_files/quote_with_two_products_and_customer_and_custom_price.php
     *
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default CNY
     * @magentoConfigFixture current_store currency/options/allow CNY,USD
     */
    public function testGetItemPriceConvert()
    {
        $this->session->setCustomerId(1);
        $customPrice = $this->block->getItemPrice($this->productRepository->get('simple'));
        $this->assertStringContainsString('84.00', $customPrice);
        $price = $this->block->getItemPrice($this->productRepository->get('custom-design-simple-product'));
        $this->assertStringContainsString('70.00', $price);
    }
}
