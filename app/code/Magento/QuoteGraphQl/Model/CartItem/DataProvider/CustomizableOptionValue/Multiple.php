<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValueInterface;

/**
 * Multiple Option Value Data provider
 */
class Multiple implements CustomizableOptionValueInterface
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
        $selectedOptionValueData = [];
        $optionIds = $selectedOption->getValue() !== null ? explode(',', $selectedOption->getValue()) : [];

        if (0 === count($optionIds)) {
            return $selectedOptionValueData;
        }

        foreach ($optionIds as $optionId) {
            $optionValue = $option->getValueById($optionId);
            if ($optionValue) {
                $priceValueUnits = $this->priceUnitLabel->getData($optionValue->getPriceType());

                $optionDetails = [
                    self::OPTION_TYPE,
                    $option->getOptionId(),
                    $optionValue->getOptionTypeId()
                ];

                $selectedOptionValueData[] = [
                    'id' => $selectedOption->getId(),
                    'customizable_option_value_uid' => $this->uidEncoder->encode((string)implode('/', $optionDetails)),
                    'label' => $optionValue->getTitle(),
                    'value' => $optionId,
                    'price' => [
                        'type' => strtoupper($optionValue->getPriceType()),
                        'units' => $priceValueUnits,
                        'value' => $optionValue->getPrice(),
                    ],
                ];
            }
        }

        return $selectedOptionValueData;
    }
}
