<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Custom Option Data provider
 */
class CustomizableOption
{
    /**
     * @var CustomOptionValueComposite
     */
    private $customOptionValueDataProvider;

    /**
     * @param CustomOptionValueComposite $customOptionValueDataProvider
     */
    public function __construct(
        CustomOptionValueComposite $customOptionValueDataProvider
    ) {
        $this->customOptionValueDataProvider = $customOptionValueDataProvider;
    }

    /**
     * Retrieve custom option data
     *
     * @param QuoteItem $cartItem
     * @param int $optionId
     * @return array
     * @throws LocalizedException
     */
    public function getData(QuoteItem $cartItem, int $optionId): array
    {
        $product = $cartItem->getProduct();
        $option = $product->getOptionById($optionId);
        $optionType = $option->getType();

        if (!$option) {
            return [];
        }

        $selectedOption = $cartItem->getOptionByCode('option_' . $option->getId());

        $selectedOptionValueData = $this->customOptionValueDataProvider->getData(
            $optionType,
            $cartItem,
            $option,
            $selectedOption
        );

        return [
            'id' => $option->getId(),
            'label' => $option->getTitle(),
            'type' => $option->getType(),
            'values' => $selectedOptionValueData,
            'sort_order' => $option->getSortOrder(),
        ];
    }
}
