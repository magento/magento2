<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\WishlistItem\DataProvider\CustomizableOptionValue;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\Item\Option as SelectedOption;
use Magento\WishlistGraphQl\Model\WishlistItem\DataProvider\CustomizableOptionValueInterface;

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
        WishlistItem $wishlistItem,
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
        return $customizableOptionValue->getData($wishlistItem, $option, $selectedOption);
    }
}
