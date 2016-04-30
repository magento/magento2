<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Block\Customer\Wishlist;

use Magento\Wishlist\Test\Block\Customer\Wishlist\Items\Product;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Customer wishlist items block on frontend.
 */
class Items extends Block
{
    /**
     * Item product block.
     *
     * @var string
     */
    protected $itemBlock = './/li[.//a[contains(.,"%s")]]';

    /**
     * Selector for 'Remove item' button.
     *
     * @var string
     */
    protected $remove = '[data-role="remove"]';

    /**
     * Get item product block.
     *
     * @param FixtureInterface $product
     * @return Product
     */
    public function getItemProduct(FixtureInterface $product)
    {
        $productBlock = sprintf($this->itemBlock, $product->getName());
        return $this->blockFactory->create(
            'Magento\Wishlist\Test\Block\Customer\Wishlist\Items\Product',
            ['element' => $this->_rootElement->find($productBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
