<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Block\Checkout;

use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Framework\App\Area;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Verify default items template
 */
class OverviewTest extends TestCase
{
    /**
     * @var Overview
     */
    private $block;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var ProductRepository
     */
    private $product;

    /**
     * @var Item
     */
    private $item;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)
            ->createBlock(
                Overview::class,
                'checkout_overview',
                [
                    'data' => [
                        'renderer_template' => 'Magento_Multishipping::checkout/item/default.phtml',
                        'row_renderer_template' => 'Magento_Multishipping::checkout/overview/item.phtml',
                    ],
                ]
            );

        $this->block->addChild('renderer.list', RendererList::class);
        $this->block->getChildBlock(
            'renderer.list'
        )->addChild(
            'default',
            Renderer::class,
            ['template' => 'cart/item/default.phtml']
        );
        $this->quote = $this->objectManager->create(Quote::class);
        $this->product = $this->objectManager->create(ProductRepository::class);
        $this->item = $this->objectManager->create(Item::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetRowItemHtml()
    {
        $product = $this->product->get('simple');
        $item = $this->item->setProduct($product);
        $item->setQuote($this->quote);
        // assure that default renderer was obtained
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//*[contains(@class,"product") and contains(@class,"name")]/a',
                $this->block->getRowItemHtml($item)
            )
        );
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/customer_quote_with_items_simple_product_options.php
     */
    public function testLinkOptionalProductFileItemHtml()
    {
        $quote = $this->quote->load('customer_quote_product_custom_options', 'reserved_order_id');
        $item = current($quote->getAllItems());
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                '//dd/a[contains(text(), "test.jpg")]',
                $this->block->getRowItemHtml($item)
            )
        );
    }
}
