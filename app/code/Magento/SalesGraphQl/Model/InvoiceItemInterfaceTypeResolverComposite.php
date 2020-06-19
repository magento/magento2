<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 *  Composite class to resolve invoice item type
 */
class InvoiceItemInterfaceTypeResolverComposite implements TypeResolverInterface
{
    /**
     * @var TypeResolverInterface[]
     */
    private $invoiceItemTypeResolvers = [];

    /**
     * @param TypeResolverInterface[] $invoiceItemTypeResolvers
     */
    public function __construct(array $invoiceItemTypeResolvers = [])
    {
        $this->invoiceItemTypeResolvers = $invoiceItemTypeResolvers;
    }

    /**
     * Resolve item type of an invoice through composite resolvers
     *
     * @param array $data
     * @return string
     * @throws GraphQlInputException
     */
    public function resolveType(array $data): string
    {
        $resolvedType = null;

        foreach ($this->invoiceItemTypeResolvers as $invoiceItemTypeResolver) {
            if (!isset($data['product_type'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in sales item data', ['product_type'])
                );
            }
            $resolvedType = $invoiceItemTypeResolver->resolveType($data);
            if (!empty($resolvedType)) {
                return $resolvedType;
            }
        }

        throw new GraphQlInputException(
            __('Concrete type for %1 not implemented', ['InvoiceItemInterface'])
        );
    }
}
