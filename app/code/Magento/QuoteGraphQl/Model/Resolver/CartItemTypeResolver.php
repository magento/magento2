<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * {@inheritdoc}
 */
class CartItemTypeResolver implements TypeResolverInterface
{
    /**
     * @var string[]
     */
    private $cartItemTypes = [];

    /**
     * @param TypeResolverInterface[] $cartItemTypes
     */
    public function __construct(array $cartItemTypes = [])
    {
        $this->cartItemTypes = $cartItemTypes;
    }

    /**
     * {@inheritdoc}
     * @throws GraphQlInputException
     */
    public function resolveType(array $data) : string
    {
        if (!isset($data['product'])) {
            return '';
        }

        $productData = $data['product'];

        if (!isset($productData['type_id'])) {
            return '';
        }

        $productTypeId = $productData['type_id'];

        if (!isset($this->cartItemTypes[$productTypeId])) {
            return '';
        }

        return $this->cartItemTypes[$productTypeId];
    }
}
