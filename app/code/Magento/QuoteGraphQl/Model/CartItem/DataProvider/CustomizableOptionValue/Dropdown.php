<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\Select as SelectOptionType;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValueInterface;

/**
 * @inheritdoc
 */
class Dropdown implements CustomizableOptionValueInterface
{
    /**
     * @var PriceUnitLabel
     */
    private $priceUnitLabel;

    /**
     * @param PriceUnitLabel $priceUnitLabel
     */
    public function __construct(
        PriceUnitLabel $priceUnitLabel
    ) {
        $this->priceUnitLabel = $priceUnitLabel;
    }

    /**
     * @inheritdoc
     */
    public function getData(
        QuoteItem $cartItem,
        Option $option,
        SelectedOption $selectedOption
    ): array {
        /** @var SelectOptionType $optionTypeRenderer */
        $optionTypeRenderer = $option->groupFactory($option->getType())
            ->setOption($option)
            ->setConfigurationItemOption($selectedOption);

        $selectedValue = $selectedOption->getValue();
        $optionValue = $option->getValueById($selectedValue);
        $optionPriceType = (string)$optionValue->getPriceType();
        $priceValueUnits = $this->priceUnitLabel->getData($optionPriceType);

        $selectedOptionValueData = [
            'id' => $selectedOption->getId(),
            'label' => $optionTypeRenderer->getFormattedOptionValue($selectedValue),
            'value' => $selectedValue,
            'price' => [
                'type' => strtoupper($optionPriceType),
                'units' => $priceValueUnits,
                'value' => $optionValue->getPrice(),
            ]
        ];
        return [$selectedOptionValueData];
    }
}
