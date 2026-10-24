<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\TypeResolver;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class AttributeOption implements TypeResolverInterface
{
    private const TYPE = 'AttributeOptionMetadata';

    /**
     * @var TypeResolverInterface[]
     */
    private array $typeResolvers;

    /**
     * @param array $typeResolvers
     */
    public function __construct(array $typeResolvers = [])
    {
        $this->typeResolvers = $typeResolvers;
    }

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        return self::TYPE;
    }
}
