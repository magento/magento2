<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
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
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Cart */
    private $block;

    /** @var Quote */
    private $session;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Cart::class);
        $this->session = $this->objectManager->get(Quote::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->session->clearStorage();

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
}
