<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer;

/**
 * Class checks item block rendering with simple product and simple product with options.
 *
 * @see \Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item
 */
class ItemTest extends AbstractItemTest
{
    /**
     * @magentoDataFixture Magento/Checkout/_files/customer_quote_with_items_simple_product_options.php
     * @return void
     */
    public function testRenderProductOptions(): void
    {
        $this->processRender();
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @return void
     */
    public function testRenderSimpleProduct(): void
    {
        $this->processRender();
    }
}
