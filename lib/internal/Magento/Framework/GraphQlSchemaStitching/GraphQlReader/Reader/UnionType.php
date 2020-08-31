<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader;

use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\FieldMetaReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DocReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\CacheAnnotationReader;

/**
 * Composite configuration reader to handle the union object type meta
 */
class UnionType implements TypeMetaReaderInterface
{
    public const GRAPHQL_UNION = 'graphql_union';

    /**
     * @var FieldMetaReader
     */
    private $fieldMetaReader;

    /**
     * @var DocReader
     */
    private $docReader;

    /**
     * @var CacheAnnotationReader
     */
    private $cacheAnnotationReader;

    /**
     * @param FieldMetaReader $fieldMetaReader
     * @param DocReader $docReader
     * @param CacheAnnotationReader|null $cacheAnnotationReader
     */
    public function __construct(
        FieldMetaReader $fieldMetaReader,
        DocReader $docReader,
        CacheAnnotationReader $cacheAnnotationReader = null
    ) {
        $this->fieldMetaReader = $fieldMetaReader;
        $this->docReader = $docReader;
        $this->cacheAnnotationReader = $cacheAnnotationReader ?? \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CacheAnnotationReader::class);
    }

    /**
     * @inheritDoc
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta): array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\UnionType) {
            $typeName = $typeMeta->name;
            $result = [
                'name' => $typeName,
                'type' => self::GRAPHQL_UNION,
                'types' => [],
            ];

            $unionTypeResolver = $this->getUnionTypeResolver($typeMeta);
            if (!empty($unionTypeResolver)) {
                $result['typeResolver'] = $unionTypeResolver;
            }

            foreach ($typeMeta->getTypes() as $type) {
                $result['types'][] = $type->name;
            }

            if ($this->docReader->read($typeMeta->astNode->directives)) {
                $result['description'] = $this->docReader->read($typeMeta->astNode->directives);
            }

            return $result;
        } else {
            return [];
        }
    }

    /**
     * Retrieve the union type resolver if it exists from the meta data
     *
     * @param \GraphQL\Type\Definition\UnionType $unionTypeMeta
     * @return string
     */
    private function getUnionTypeResolver(\GraphQL\Type\Definition\UnionType $unionTypeMeta): string
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $unionTypeMeta->astNode->directives;
        foreach ($directives as $directive) {
            if ($directive->name->value == 'typeResolver') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'class') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return '';
    }
}
