<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Block;

use Magento\Framework\View\Element\Template;

/**
 * Wishlist js plugin initialization block
 *
 * @api
 * @since 100.1.0
 */
class AddToWishlist extends Template
{
    /**
     * Product types
     *
     * @var array|null
     */
    private $productTypes;

    /**
     * Returns wishlist widget options
     *
     * @return array
     * @since 100.1.0
     */
    public function getWishlistOptions()
    {
        return [
            'productType' => $this->getProductTypes(),
            'isProductList' => (bool)$this->getData('is_product_list')
        ];
    }

    /**
     * Returns an array of product types
     *
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getProductTypes()
    {
        if ($this->productTypes === null) {
            $this->productTypes = [];
            $block = $this->getLayout()->getBlock($this->getProductListBlockName());
            if ($block) {
                $productCollection = $block->getLoadedProductCollection();
                $productTypes = [];
                /** @var $product \Magento\Catalog\Model\Product */
                foreach ($productCollection as $product) {
                    $productTypes[] = $this->escapeHtml($product->getTypeId());
                }
                $this->productTypes = array_unique($productTypes);
            }
        }
        return $this->productTypes;
    }

    /**
     * Get product list block name in layout
     *
     * @return string
     */
    private function getProductListBlockName(): string
    {
        return $this->getData('product_list_block') ?: 'category.products.list';
    }

    /**
     * @inheritDoc
     *
     * @since 100.1.0
     */
    protected function _toHtml()
    {
        if (!$this->getProductTypes()) {
            return '';
        }
        return parent::_toHtml();
    }
}
