<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader;

use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DocReader;

/**
 * Composite configuration reader to handle the enum type meta
 */
class ScalarType implements TypeMetaReaderInterface
{
    public const GRAPHQL_SCALAR = 'graphql_scalar';

    /**
     * @var DocReader
     */
    private $docReader;

    /**
     * @param DocReader $docReader
     */
    public function __construct(
        DocReader $docReader
    ) {
        $this->docReader = $docReader;
    }

    /**
     * @inheritDoc
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\ScalarType) {
            $result = [
                'name' => $typeMeta->name,
                'type' => self::GRAPHQL_SCALAR,
            ];

            if ($this->docReader->read($typeMeta->astNode->directives)) {
                $result['description'] = $this->docReader->read($typeMeta->astNode->directives);
            }

            $typeResolver = $this->getTypeResolver($typeMeta);
            if (!empty($typeResolver)) {
                $result['implementation'] = $typeResolver;
            }
            return $result;

        } else {
            return [];
        }
    }

    /**
     * Retrieve the type resolver class name
     *
     * @param \GraphQL\Type\Definition\Type $interfaceTypeMeta
     * @return string
     */
    private function getTypeResolver(\GraphQL\Type\Definition\Type $interfaceTypeMeta) : string
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $interfaceTypeMeta->astNode->directives;
        foreach ($directives as $directive) {
            if ($directive->name->value == 'implementation') {
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
