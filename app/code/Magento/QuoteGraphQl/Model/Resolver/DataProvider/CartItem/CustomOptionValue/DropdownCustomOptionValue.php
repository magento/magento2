<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem\CustomOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType as DefaultOptionType;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem\CustomOptionPriceUnitLabel;

/**
 * Dropdown Custom Option Value Data provider
 */
class DropdownCustomOptionValue implements CustomOptionValueInterface
{
    /**
     * @var CustomOptionPriceUnitLabel
     */
    private $customOptionPriceUnitLabel;

    /**
     * @param CustomOptionPriceUnitLabel $customOptionPriceUnitLabel
     */
    public function __construct(
        CustomOptionPriceUnitLabel $customOptionPriceUnitLabel
    ) {
        $this->customOptionPriceUnitLabel = $customOptionPriceUnitLabel;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NoSuchEntityException
     */
    public function getData(
        QuoteItem $cartItem,
        Option $option,
        SelectedOption $selectedOption,
        DefaultOptionType $optionTypeRenderer
    ): array {
        $selectedValue = $selectedOption->getValue();
        $optionValue = $option->getValueById($selectedValue);
        $optionPriceType = (string) $optionValue->getPriceType();
        $priceValueUnits = $this->customOptionPriceUnitLabel->getData($optionPriceType);

        $selectedOptionValueData = [
            'id' => $selectedOption->getId(),
            'label' => $optionTypeRenderer->getFormattedOptionValue($selectedValue),
            'price' => [
                'type' => strtoupper($optionPriceType),
                'units' => $priceValueUnits,
                'value' => $optionValue->getPrice(),
            ]
        ];

        return [$selectedOptionValueData];
    }
}
