<?php
declare(strict_types=1);
/**
 * WishlistItemsProductsResolver
 *
 * @copyright Copyright © 2018 brandung GmbH & Co. KG. All rights reserved.
 * @author    david.verholen@brandung.de
 */

namespace Magento\WishlistGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\WishlistGraphQl\Model\WishlistItemsProductDataProvider;

class WishlistItemsProductsResolver implements ResolverInterface
{
    /**
     * @var WishlistItemsProductDataProvider
     */
    private $productDataProvider;

    public function __construct(WishlistItemsProductDataProvider $productDataProvider)
    {
        $this->productDataProvider = $productDataProvider;
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
        if (!isset($value['product_id'])) {
            throw new GraphQlInputException(
                __('Missing key %1 in wishlist item data', ['product_id'])
            );
        }
        return $this->productDataProvider->getProductDataById($value['product_id']);
    }
}
