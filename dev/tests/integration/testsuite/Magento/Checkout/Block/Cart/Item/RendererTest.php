<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Block\Cart\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class RendererTest
 * @package Magento\Checkout\Block\Cart\Item
 * @magentoAppArea frontend
 */
class RendererTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var mixed
     */
    private $renderer;

    /**
     * @var mixed
     */
    public $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->renderer = $this->objectManager->get(Renderer::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote($reservedOrderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);

        /** @var Quote[] $items */
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return $items[0];
    }

    /**
     * Gets \Magento\Quote\Model\Quote\Item from \Magento\Quote\Model\Quote by product id
     *
     * @param Quote $quote
     * @param string|int $productId
     *
     * @return Item|null
     */
    private function _getQuoteItemIdByProductId($quote, $productId)
    {
        /** @var $quoteItems Item[] */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }
        return null;
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/cart_with_simple_product_and_custom_option_text_area.php
     */
    public function testTextAreaCustomOption()
    {
        $quote = $this->getQuote('test_order_item_with_custom_option_text_area');

        /** @var $product Product */
        $product = $this->productRepository->get('simple_with_custom_option_text_area');

        $quoteItem = $this->_getQuoteItemIdByProductId($quote, $product->getId());
        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product with custom option text area');

        $template = 'Magento_Checkout::cart/item/default.phtml';
        $this->renderer->setTemplate($template);
        $this->renderer->setItem($quoteItem);

        $priceBlock = $this->objectManager->create(\Magento\Checkout\Block\Item\Price\Renderer::class);
        $this->renderer->getLayout()->setBlock('checkout.item.price.unit', $priceBlock);
        $this->renderer->getLayout()->setBlock('checkout.item.price.row', $priceBlock);
        $html = $this->renderer->toHtml();

        $this->assertMatchesRegularExpression(<<<EOT
/Test product simple with
custom option text area
with more 50 characters/
EOT
, $html);
    }
}
