<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\Text as TextOptionType;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValueInterface;

class File implements CustomizableOptionValueInterface
{
    /**
     * @param PriceUnitLabel $priceUnitLabel
     */
    public function __construct(
        private readonly PriceUnitLabel $priceUnitLabel
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getData(
        QuoteItem $cartItem,
        Option $option,
        SelectedOption $selectedOption
    ): array {
        /** @var TextOptionType $optionTypeRenderer */
        $optionTypeRenderer = $option->groupFactory($option->getType());
        $optionTypeRenderer->setOption($option);
        $priceValueUnits = $this->priceUnitLabel->getData($option->getPriceType());
        $optionTypeRenderer->setData('configuration_item_option', $selectedOption);
        $value = $optionTypeRenderer->getFormattedOptionValue($selectedOption->getValue());
        $selectedOptionValueData = [
            'id' => $selectedOption->getId(),
            'label' => $option->getTitle(),
            'value' => $value,
            'price' => [
                'type' => strtoupper($option->getPriceType()),
                'units' => $priceValueUnits,
                'value' => $option->getPrice(),
            ],
        ];
        return [$selectedOptionValueData];
    }
}
