<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model;

use Magento\Framework\GraphQl\Config\Data\TypeResolverInterface;
use Magento\Framework\GraphQl\Type\Entity\MapperInterface;

/**
 * Class CustomizableOptionTypeResolver
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
    public function resolveType(array $data)
    {
        $map = $this->mapper->getMappedTypes(self::ENTITY_TYPE);
        if (!isset($map[$data['type']]) || !isset($map[$data['type']])) {
            return null;
        }

        return $map[$data['type']];
    }
}
