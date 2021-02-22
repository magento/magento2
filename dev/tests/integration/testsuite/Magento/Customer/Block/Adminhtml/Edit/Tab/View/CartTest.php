<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Escaper;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class CartTest
 *
 * @magentoAppArea adminhtml
 */
class CartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Shopping cart.
     *
     * @var Cart
     */
    private $block;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Execute per test initialization.
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('adminhtml');

        $this->coreRegistry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);

        $this->block = $objectManager->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\View\Cart::class,
            '',
            ['coreRegistry' => $this->coreRegistry, 'data' => ['website_id' => 1]]
        );
        $this->block->getPreparedCollection();
        $this->escaper = $objectManager->get(Escaper::class);
    }

    /**
     * Execute per test cleanup.
     */
    protected function tearDown(): void
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Verify that the Url for a product row in the cart grid is correct.
     */
    public function testGetRowUrl()
    {
        $row = new \Magento\Framework\DataObject(['product_id' => 1]);
        $this->assertStringContainsString('catalog/product/edit/id/1', $this->block->getRowUrl($row));
    }

    /**
     * Verify that the headers in the cart grid are visible.
     */
    public function testGetHeadersVisibility()
    {
        $this->assertTrue($this->block->getHeadersVisibility());
    }

    /**
     * Verify the basic content of an empty cart.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testToHtmlEmptyCart()
    {
        $this->assertEquals(0, $this->block->getCollection()->getSize());
        $this->assertStringContainsString($this->escaper->escapeHtml('There are no items in customer\'s shopping cart.'), $this->block->toHtml());
    }

    /**
     * Verify the Html content for a single item in the customer's cart.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/quote.php
     */
    public function testToHtmlCartItem()
    {
        $html = $this->block->toHtml();
        $this->assertStringContainsString('Simple Product', $html);
        $this->assertStringContainsString('simple', $html);
        $this->assertStringContainsString('$10.00', $html);
        $this->assertStringContainsString($this->escaper->escapeHtmlAttr('catalog/product/edit/id/1'), $html);
    }
}
