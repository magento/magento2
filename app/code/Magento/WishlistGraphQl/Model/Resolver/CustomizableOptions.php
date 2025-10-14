<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\WishlistGraphQl\Model\WishlistItem\DataProvider\CustomizableOption;

/**
 * @inheritdoc
 */
class CustomizableOptions implements ResolverInterface
{
    /**
     * @var CustomizableOption
     */
    private $customizableOption;

    /**
     * @param CustomizableOption $customizableOption
     */
    public function __construct(
        CustomizableOption $customizableOption
    ) {
        $this->customizableOption = $customizableOption;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['itemModel'])) {
            throw new LocalizedException(__('"itemModel" value should be specified'));
        }

        /** @var WishlistItem $wishlistItem */
        $wishlistItem = $value['itemModel'];
        $wishlistItemOption = $wishlistItem->getOptionByCode('option_ids');

        if (null === $wishlistItemOption) {
            return [];
        }

        $customizableOptionsData = [];
        $customizableOptionIds = explode(',', $wishlistItemOption->getValue() ?? '');

        foreach ($customizableOptionIds as $customizableOptionId) {
            $customizableOption = $this->customizableOption->getData(
                $wishlistItem,
                (int)$customizableOptionId
            );
            $customizableOptionsData[] = $customizableOption;
        }
        return $customizableOptionsData;
    }
}
