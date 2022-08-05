<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WishlistGraphQl\Model\Resolver\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * Resolving the wishlist item type
 */
class WishlistItemType implements TypeResolverInterface
{
    /**
     * @var array
     */
    private $supportedTypes = [];

    /**
     * @param array $supportedTypes
     */
    public function __construct(array $supportedTypes = [])
    {
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * Resolving wishlist item type
     *
     * @param array $data
     *
     * @return string
     *
     * @throws LocalizedException
     */
    public function resolveType(array $data): string
    {
        if (!$data['model'] instanceof ProductInterface) {
            throw new LocalizedException(__('"model" should be a "%instance" instance', [
                'instance' => ProductInterface::class
            ]));
        }

        $productTypeId = $data['model']->getTypeId();

        if (!isset($this->supportedTypes[$productTypeId])) {
            throw new LocalizedException(
                __('Product "%product_type" type is not supported', ['product_type' => $productTypeId])
            );
        }

        return $this->supportedTypes[$productTypeId];
    }
}
