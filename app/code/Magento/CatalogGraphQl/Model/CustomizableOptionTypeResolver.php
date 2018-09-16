<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;

/**
 * Resolve the CustomizableOptionType for graphql schema
 */
class CustomizableOptionTypeResolver implements TypeResolverInterface
{
    const ENTITY_TYPE = 'customizable_options';

    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @param MapperInterface $mapper
     */
    public function __construct(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveType(array $data) : string
    {
        $map = $this->mapper->getMappedTypes(self::ENTITY_TYPE);
        if (isset($map[$data['type']])) {
            return $map[$data['type']];
        }
        return '';
    }
}
