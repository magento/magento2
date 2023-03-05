<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist sidebar block
 */
namespace Magento\Wishlist\Block\Customer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Render;
use Magento\Wishlist\Block\AbstractBlock;

/**
 * @api
 * @since 100.0.2
 */
class Sidebar extends AbstractBlock
{
    /**
     * Retrieve block title
     *
     * @return Phrase
     */
    public function getTitle()
    {
        return __('My Wish List');
    }

    /**
     * Return HTML block content
     *
     * @param Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @since 100.1.0
     */
    public function getProductPriceHtml(
        Product $product,
        $priceType,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        $price = '';

        $priceRender = $this->getPriceRender();
        if ($priceRender) {
            $price = $priceRender->render($priceType, $product, $arguments);
        }

        return $price;
    }

    /**
     * Get price render block
     *
     * @return Render
     */
    private function getPriceRender()
    {
        /** @var Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                Render::class,
                'product.price.render.default',
                [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                    ],
                ]
            );
        }
        return $priceRender;
    }
}
