<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem\CustomOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;

/**
 * Custom Option Value Data provider
 */
interface CustomOptionValueInterface
{
    /**
     * Custom Option Type Data Provider
     *
     * @param QuoteItem $cartItem
     * @param Option $option
     * @param SelectedOption $selectedOption
     * @param DefaultType $optionTypeRenderer
     * @return array
     */
    public function getData(
        QuoteItem $cartItem,
        Option $option,
        SelectedOption $selectedOption,
        DefaultType $optionTypeRenderer
    ): array;
}
