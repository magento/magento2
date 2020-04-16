<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\ViewModel\Order\Email\Items\Creditmemo;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Creditmemo\Item;

/**
 * Credimemo email item price render
 */
class ItemPriceRender implements ArgumentInterface
{
    /**
     * Get the html for item price
     *
     * @param Template $block
     * @param Item $item
     * @return string
     */
    public function render(Template $block, Item $item): string
    {
        $itemPriceBlock = $block->getLayout()->getBlock('item_price');
        $itemPriceBlock->setItem($item);
        return $itemPriceBlock->toHtml();
    }
}
