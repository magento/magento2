<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use \GraphQL\Type\Definition\ResolveInfo;
use \GraphQL\Type\Definition\ObjectType;
use \GraphQL\Type\Schema;
use Magento\GraphQl\Model\Type\Generator;
use Magento\GraphQl\Model\ResolverFactory;

/**
 * Generates query field and concrete types for GraphQL scehma
 */
class SchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * @var Generator
     */
    private $typeGenerator;

    /**
     * @var ResolverFactory
     */
    private $resolverFactory;

    /**
     * @param Generator $typeGenerator
     * @param ResolverFactory $resolverFactory
     */
    public function __construct(Generator $typeGenerator, ResolverFactory $resolverFactory)
    {
        $this->typeGenerator = $typeGenerator;
        $this->resolverFactory = $resolverFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function generate()
    {
        $schemaConfig = $this->typeGenerator->generateTypes('Query');
        $config = new ObjectType([
            'name' => 'Query',
            'fields' => $schemaConfig['fields'],
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                $resolver = $this->resolverFactory->create(ucfirst($info->fieldName));
                return $resolver->resolve($args, $info);
            }
        ]);
        $schema = new Schema(['query' => $config, 'types' => $schemaConfig['types']]);
        return $schema;
    }
}
