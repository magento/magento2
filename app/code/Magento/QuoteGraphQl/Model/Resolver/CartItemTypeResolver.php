<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class CartItemTypeResolver implements TypeResolverInterface
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
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (!isset($data['product'])) {
            throw new LocalizedException(__('Missing key "product" in cart data'));
        }
        $productData = $data['product'];

        if (!isset($productData['type_id'])) {
            throw new LocalizedException(__('Missing key "type_id" in product data'));
        }
        $productTypeId = $productData['type_id'];

        if (!isset($this->supportedTypes[$productTypeId])) {
            throw new LocalizedException(
                __('Product "%product_type" type is not supported', ['product_type' => $productTypeId])
            );
        }
        return $this->supportedTypes[$productTypeId];
    }
}
