<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\WishlistItem\DataProvider;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Wishlist\Model\Item as WishlistItem;

/**
 * Custom Option Data provider
 */
class CustomizableOption
{
    /**
     * Option type name
     */
    private const OPTION_TYPE = 'custom-option';

    /**
     * @var CustomizableOptionValueInterface
     */
    private $customizableOptionValue;

    /**
     * @var Uid
     */
    private $uidEncoder;

    /**
     * @param CustomizableOptionValueInterface $customOptionValueDataProvider
     * @param Uid $uidEncoder
     */
    public function __construct(
        CustomizableOptionValueInterface $customOptionValueDataProvider,
        Uid $uidEncoder
    ) {
        $this->customizableOptionValue = $customOptionValueDataProvider;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * Retrieve custom option data
     *
     * @param WishlistItem $wishlistItem
     * @param int $optionId
     * @return array
     * @throws LocalizedException
     */
    public function getData(WishlistItem $wishlistItem, int $optionId): array
    {
        $product = $wishlistItem->getProduct();
        $option = $product->getOptionById($optionId);

        if (!$option) {
            return [];
        }

        $selectedOption = $wishlistItem->getOptionByCode('option_' . $option->getId());

        $selectedOptionValueData = $this->customizableOptionValue->getData(
            $wishlistItem,
            $option,
            $selectedOption
        );

        return [
            'id' => $option->getId(),
            'customizable_option_uid' => $this->uidEncoder->encode((string) self::OPTION_TYPE . '/' . $option->getId()),
            'label' => $option->getTitle(),
            'type' => $option->getType(),
            'values' => $selectedOptionValueData,
            'sort_order' => $option->getSortOrder(),
            'is_required' => $option->getIsRequire(),
        ];
    }
}
