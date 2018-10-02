<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as SelectedOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOptionValueInterface;

/**
 * @inheritdoc
 */
class Composite implements CustomizableOptionValueInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomizableOptionValueInterface[]
     */
    private $customizableOptionValues;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param CustomizableOptionValueInterface[] $customizableOptionValues
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $customizableOptionValues = []
    ) {
        $this->objectManager = $objectManager;
        $this->customizableOptionValues = $customizableOptionValues;
    }

    /**
     * @inheritdoc
     */
    public function getData(
        QuoteItem $cartItem,
        Option $option,
        SelectedOption $selectedOption
    ): array {
        $optionType = $option->getType();

        if (!array_key_exists($optionType, $this->customizableOptionValues)) {
            throw new GraphQlInputException(__('Option type "%1" is not supported', $optionType));
        }
        $customizableOptionValueClassName = $this->customizableOptionValues[$optionType];

        $customizableOptionValue = $this->objectManager->get($customizableOptionValueClassName);
        if (!$customizableOptionValue instanceof CustomizableOptionValueInterface) {
            throw new LocalizedException(
                __('%1 doesn\'t implement CustomizableOptionValueInterface', $customizableOptionValueClassName)
            );
        }
        return $customizableOptionValue->getData($cartItem, $option, $selectedOption);
    }
}
