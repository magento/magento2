<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\Select as SelectOptionType;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValueInterface;

/**
 * @inheritdoc
 */
class Dropdown implements CustomizableOptionValueInterface
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'custom-option';

    /**
     * @var PriceUnitLabel
     */
    private $priceUnitLabel;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param PriceUnitLabel $priceUnitLabel
     * @param Uid|null $uidEncoder
     */
    public function __construct(
        PriceUnitLabel $priceUnitLabel,
        Uid $uidEncoder = null
    ) {
        $this->priceUnitLabel = $priceUnitLabel;
        $this->uidEncoder = $uidEncoder ?: ObjectManager::getInstance()
            ->get(Uid::class);
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

        $selectedOptionValues = [];
        $selectedValue = $selectedOption->getValue();
        $optionValue = $option->getValueById($selectedValue);
        if ($optionValue) {
            $optionPriceType = (string)$optionValue->getPriceType();
            $priceValueUnits = $this->priceUnitLabel->getData($optionPriceType);

            $optionDetails = [
                self::OPTION_TYPE,
                $option->getOptionId(),
                $optionValue->getOptionTypeId()
            ];

            $selectedOptionValues[] = [
                'id' => $selectedOption->getId(),
                'customizable_option_value_uid' => $this->uidEncoder->encode((string)implode('/', $optionDetails)),
                'label' => $optionTypeRenderer->getFormattedOptionValue($selectedValue),
                'value' => $selectedValue,
                'price' => [
                    'type' => strtoupper($optionPriceType),
                    'units' => $priceValueUnits,
                    'value' => $optionValue->getPrice(),
                ]
            ];
        }
        return $selectedOptionValues;
    }
}
