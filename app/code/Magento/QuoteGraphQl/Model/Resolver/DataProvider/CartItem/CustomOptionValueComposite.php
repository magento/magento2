<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType as DefaultOptionType;
use Magento\Catalog\Model\Product\Option\Type\Select as SelectOptionType;
use Magento\Catalog\Model\Product\Option\Type\Text as TextOptionType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\Resolver\DataProvider\CartItem\CustomOptionValue\CustomOptionValueInterface;

/**
 * Custom Option Value Composite Data provider
 */
class CustomOptionValueComposite
{
    /**
     * @var CustomOptionValueInterface[]
     */
    private $customOptionValueTypeProviders;

    /**
     * @param array $customOptionValueTypeProviders
     */
    public function __construct(
        array $customOptionValueTypeProviders
    ) {
        $this->customOptionValueTypeProviders = $customOptionValueTypeProviders;
    }

    /**
     * Retrieve custom option values data
     *
     * @param string $optionType
     * @param $cartItem
     * @param $option
     * @param $selectedOption
     * @return array
     * @throws LocalizedException
     */
    public function getData(
        string $optionType,
        QuoteItem $cartItem,
        Option $option,
        SelectedOption $selectedOption
    ): array {
        if (!array_key_exists($optionType, $this->customOptionValueTypeProviders)) {
            throw new LocalizedException(__('Option type "%1" is not supported', $optionType));
        }

        /** @var SelectOptionType|TextOptionType|DefaultOptionType $optionTypeRenderer */
        $optionTypeRenderer = $option->groupFactory($optionType)
            ->setOption($option)
            ->setConfigurationItem($cartItem)
            ->setConfigurationItemOption($selectedOption);

        $customOptionValueTypeProvider = $this->customOptionValueTypeProviders[$optionType];

        return $customOptionValueTypeProvider->getData($cartItem, $option, $selectedOption, $optionTypeRenderer);
    }
}
