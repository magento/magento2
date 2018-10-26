<?php
declare(strict_types=1);
/**
 * WishlistItemTypeResolver
 *
 * @copyright Copyright © 2018 brandung GmbH & Co. KG. All rights reserved.
 * @author    david.verholen@brandung.de
 */

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Wishlist\Model\Item;
use Magento\WishlistGraphQl\Model\WishlistItemsDataProvider;

class WishlistItemsResolver implements ResolverInterface
{
    /**
     * @var WishlistItemsDataProvider
     */
    private $wishlistItemsDataProvider;

    public function __construct(WishlistItemsDataProvider $wishlistItemsDataProvider)
    {
        $this->wishlistItemsDataProvider = $wishlistItemsDataProvider;
    }

    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return mixed|Value
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return array_map(function (Item $wishlistItem) {
            return [
                'id' => $wishlistItem->getId(),
                'qty' => $wishlistItem->getData('qty'),
                'description' => (string)$wishlistItem->getDescription(),
                'added_at' => $wishlistItem->getAddedAt(),
                'product_id' => (int)$wishlistItem->getProductId()
            ];
        }, $this->wishlistItemsDataProvider->getWishlistItemsForCustomer($context->getUserId()));
    }
}
