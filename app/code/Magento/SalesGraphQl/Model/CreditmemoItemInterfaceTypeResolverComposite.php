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
 * Composite resolver for credit memo item
 */
class CreditmemoItemInterfaceTypeResolverComposite implements TypeResolverInterface
{
    /**
     * @var TypeResolverInterface[]
     */
    private $creditmemoItemTypeResolvers = [];

    /**
     * @param TypeResolverInterface[] $creditMemoItemTypeResolvers
     */
    public function __construct(array $creditMemoItemTypeResolvers = [])
    {
        $this->creditmemoItemTypeResolvers = $creditMemoItemTypeResolvers;
    }

    /**
     * Resolve item type of an credit memo through composite resolvers
     *
     * @param array $data
     * @return string
     * @throws GraphQlInputException
     */
    public function resolveType(array $data): string
    {
        foreach ($this->creditmemoItemTypeResolvers as $creditMemoTypeResolver) {
            if (!isset($data['product_type'])) {
                throw new GraphQlInputException(
                    __('Missing key %1 in sales item data', ['product_type'])
                );
            }
            $resolvedType = $creditMemoTypeResolver->resolveType($data);
            if (!empty($resolvedType)) {
                return $resolvedType;
            }
        }

        throw new GraphQlInputException(
            __('Concrete type for %1 not implemented', ['CreditmemoItemInterface'])
        );
    }
}
