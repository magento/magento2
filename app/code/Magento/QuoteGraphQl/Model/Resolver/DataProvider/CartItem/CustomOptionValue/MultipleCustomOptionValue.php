<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem\CustomOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem\CustomOptionPriceUnitLabel;

/**
 * Multiple Option Value Data provider
 */
class MultipleCustomOptionValue implements CustomOptionValueInterface
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
        DefaultType $optionTypeRenderer
    ): array {
        $selectedOptionValueData = [];
        $optionIds = explode(',', $selectedOption->getValue());

        if (0 === count($optionIds)) {
            return $selectedOptionValueData;
        }

        foreach ($optionIds as $optionId) {
            $optionValue = $option->getValueById($optionId);
            $priceValueUnits = $this->customOptionPriceUnitLabel->getData($optionValue->getPriceType());

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
