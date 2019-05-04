<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

/**
 * Reads fields and possible arguments from a meta field
 */
class FieldMetaReader
{
    /**
     * @var TypeMetaWrapperReader
     */
    private $typeMetaReader;

    /**
     * @var DocReader
     */
    private $docReader;

    /**
     * @var CacheTagReader
     */
    private $cacheTagReader;

    /**
     * @param TypeMetaWrapperReader $typeMetaReader
     * @param DocReader $docReader
     * @param CacheTagReader|null $cacheTagReader
     */
    public function __construct(
        TypeMetaWrapperReader $typeMetaReader,
        DocReader $docReader,
        CacheTagReader $cacheTagReader = null
    ) {
        $this->typeMetaReader = $typeMetaReader;
        $this->docReader = $docReader;
        $this->cacheTagReader = $cacheTagReader ?? \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CacheTagReader::class);
    }

    /**
     * Read field and possible arguments from a field meta
     *
     * @param \GraphQL\Type\Definition\FieldDefinition $fieldMeta
     * @return array
     */
    public function read(\GraphQL\Type\Definition\FieldDefinition $fieldMeta) : array
    {
        $fieldName = $fieldMeta->name;
        $fieldTypeMeta = $fieldMeta->getType();
        $result = [
            'name' => $fieldName,
            'arguments' => []
        ];

        $fieldResolver = $this->getFieldResolver($fieldMeta);
        if (!empty($fieldResolver)) {
            $result['resolver'] = $fieldResolver;
        }

        $result = array_merge(
            $result,
            $this->typeMetaReader->read($fieldTypeMeta, TypeMetaWrapperReader::OUTPUT_FIELD_PARAMETER)
        );

        if ($this->docReader->read($fieldMeta->astNode->directives)) {
            $result['description'] = $this->docReader->read($fieldMeta->astNode->directives);
        }

        if ($this->docReader->read($fieldMeta->astNode->directives)) {
            $result['cache'] = $this->cacheTagReader->read($fieldMeta->astNode->directives);
        }

        $arguments = $fieldMeta->args;
        foreach ($arguments as $argumentMeta) {
            $argumentName = $argumentMeta->name;
            $result['arguments'][$argumentName] = [
                'name' => $argumentName,
            ];
            if ($argumentMeta->defaultValue !== null) {
                $result['arguments'][$argumentName]['defaultValue'] = $argumentMeta->defaultValue;
            }
            $typeMeta = $argumentMeta->getType();
            $result['arguments'][$argumentName] = array_merge(
                $result['arguments'][$argumentName],
                $this->typeMetaReader->read($typeMeta, TypeMetaWrapperReader::ARGUMENT_PARAMETER)
            );

            if ($this->docReader->read($argumentMeta->astNode->directives)) {
                $result['arguments'][$argumentName]['description'] =
                    $this->docReader->read($argumentMeta->astNode->directives);
            }
        }
        return $result;
    }

    /**
     * Read resolver if an annotation with the class of the resolver is defined in the meta
     *
     * @param \GraphQL\Type\Definition\FieldDefinition $fieldMeta
     * @return string
     */
    private function getFieldResolver(\GraphQL\Type\Definition\FieldDefinition $fieldMeta) : string
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
        return '';
    }
}
