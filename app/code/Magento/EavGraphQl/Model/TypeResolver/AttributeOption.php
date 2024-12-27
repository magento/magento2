<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
