<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\TypeResolver;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

/**
 * @inheritdoc
 */
class AttributeSelectedOption implements TypeResolverInterface
{
    private const TYPE = 'AttributeSelectedOption';

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        return self::TYPE;
    }
}
