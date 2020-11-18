<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\Text as TextOptionType;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValueInterface;

/**
 * @inheritdoc
 */
class Text implements CustomizableOptionValueInterface
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
        /** @var TextOptionType $optionTypeRenderer */
        $optionTypeRenderer = $option->groupFactory($option->getType());
        $optionTypeRenderer->setOption($option);
        $priceValueUnits = $this->priceUnitLabel->getData($option->getPriceType());

        $selectedOptionValueData = [
            'id' => $selectedOption->getId(),
            'customizable_option_value_uid' => $this->uidEncoder->encode(
                self::OPTION_TYPE . '/' . $option->getOptionId()
            ),
            'label' => '',
            'value' => $optionTypeRenderer->getFormattedOptionValue($selectedOption->getValue()),
            'price' => [
                'type' => strtoupper($option->getPriceType()),
                'units' => $priceValueUnits,
                'value' => $option->getPrice(),
            ],
        ];
        return [$selectedOptionValueData];
    }
}
