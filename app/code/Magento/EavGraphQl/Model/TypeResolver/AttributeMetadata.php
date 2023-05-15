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
class AttributeMetadata implements TypeResolverInterface
{
    private const TYPE = 'AttributeMetadata';

    /**
     * @var string[]
     */
    private array $entityTypes;

    /**
     * @param array $entityTypes
     */
    public function __construct(array $entityTypes = [])
    {
        $this->entityTypes = $entityTypes;
    }

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        if (!isset($data['entity_type'])) {
            return self::TYPE;
        }
        return $this->entityTypes[$data['entity_type']] ?? self::TYPE;
    }
}
