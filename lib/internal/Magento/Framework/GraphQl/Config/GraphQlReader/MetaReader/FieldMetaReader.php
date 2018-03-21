<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader;

class FieldMetaReader
{
    /**
     * @var TypeMetaReader
     */
    private $typeMetaReader;

    /**
     * @param TypeMetaReader $typeMetaReader
     */
    public function __construct(TypeMetaReader $typeMetaReader)
    {
        $this->typeMetaReader = $typeMetaReader;
    }

    /**
     * @param \GraphQL\Type\Definition\FieldDefinition $fieldMeta
     * @return array
     */
    public function readFieldMeta(\GraphQL\Type\Definition\FieldDefinition $fieldMeta) : array
    {
        $fieldName = $fieldMeta->name;
        $fieldTypeMeta = $fieldMeta->getType();
        $result = [
            'name' => $fieldName,
            'arguments' => []
        ];

        $fieldResolver = $this->readFieldResolver($fieldMeta);
        if ($fieldResolver) {
            $result['resolver'] = $fieldResolver;
        }

        $result = array_merge(
            $result,
            $this->typeMetaReader->readTypeMeta($fieldTypeMeta, 'OutputField')
        );

        if (!empty($fieldMeta->astNode->directives) && !($fieldMeta instanceof \GraphQL\Type\Definition\ScalarType)) {
            $result['description'] = $this->readTypeDescription($fieldMeta);
        }

        $arguments = $fieldMeta->args;
        foreach ($arguments as $argumentMeta) {
            $argumentName = $argumentMeta->name;
            $result['arguments'][$argumentName] = [
                'name' => $argumentName,
            ];
            $typeMeta = $argumentMeta->getType();
            $result['arguments'][$argumentName] = array_merge(
                $result['arguments'][$argumentName],
                $this->typeMetaReader->readTypeMeta($typeMeta, 'Argument')
            );

            if (!empty($argumentMeta->astNode->directives) && !($argumentMeta instanceof \GraphQL\Type\Definition\ScalarType)) {
                $result['arguments'][$argumentName]['description'] = $this->readTypeDescription($argumentMeta);
            }
        }
        return $result;
    }

    /**
     * @param \GraphQL\Type\Definition\FieldDefinition $fieldMeta
     * @return string|null
     */
    private function readFieldResolver(\GraphQL\Type\Definition\FieldDefinition $fieldMeta) : ?string
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $fieldMeta->astNode->directives;
        foreach ($directives as $directive) {
            if ($directive->name->value == 'resolver') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'class') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Read documentation annotation for a specific type
     *
     * @param $meta
     * @return string
     */
    private function readTypeDescription($meta) : string
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $meta->astNode->directives;
        foreach ($directives as $directive) {
            if ($directive->name->value == 'doc') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'description') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return '';
    }
}
