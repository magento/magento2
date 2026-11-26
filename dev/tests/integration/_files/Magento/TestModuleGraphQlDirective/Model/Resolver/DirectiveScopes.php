<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleGraphQlDirective\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Expose the testScope directives read by the directive readers, as seen by a resolver
 */
class DirectiveScopes implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        return array_map(
            static fn (array $arguments): array => [
                'name' => $arguments['name'] ?? null,
                'tags' => $arguments['tags'] ?? [],
            ],
            $field->getDirectives()['test_scope'] ?? []
        );
    }
}
