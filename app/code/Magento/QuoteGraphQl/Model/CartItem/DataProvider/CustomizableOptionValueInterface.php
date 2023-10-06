<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider;

use Magento\Catalog\Model\Product\Option;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;

/**
 * Customizable Option Value Data provider
 *
 * @api
 */
interface CustomizableOptionValueInterface
{
    /**
     * Customizable Option Value Data Provider
     *
     * @param QuoteItem $cartItem
     * @param Option $option
     * @param SelectedOption $selectedOption
     * @return array
     */
    public function getData(
        QuoteItem $cartItem,
        Option $option,
        SelectedOption $selectedOption
    ): array;
}
