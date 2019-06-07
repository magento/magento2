<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValueInterface;

/**
 * Multiple Option Value Data provider
 */
class Multiple implements CustomizableOptionValueInterface
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
        $selectedOptionValueData = [];
        $optionIds = explode(',', $selectedOption->getValue());

        if (0 === count($optionIds)) {
            return $selectedOptionValueData;
        }

        foreach ($optionIds as $optionId) {
            $optionValue = $option->getValueById($optionId);
            $priceValueUnits = $this->priceUnitLabel->getData($optionValue->getPriceType());

            $selectedOptionValueData[] = [
                'id' => $selectedOption->getId(),
                'label' => $optionValue->getTitle(),
                'price' => [
                    'type' => strtoupper($optionValue->getPriceType()),
                    'units' => $priceValueUnits,
                    'value' => $optionValue->getPrice(),
                ],
            ];
        }

        return $selectedOptionValueData;
    }
}
