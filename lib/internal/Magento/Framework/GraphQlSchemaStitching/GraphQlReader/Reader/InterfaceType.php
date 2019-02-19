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

/**
 * Composite configuration reader to handle the interface object type meta
 */
class InterfaceType implements TypeMetaReaderInterface
{
    /**
     * @var FieldMetaReader
     */
    private $fieldMetaReader;

    /**
     * @var DocReader
     */
    private $docReader;

    /**
     * @param FieldMetaReader $fieldMetaReader
     * @param DocReader $docReader
     */
    public function __construct(FieldMetaReader $fieldMetaReader, DocReader $docReader)
    {
        $this->fieldMetaReader = $fieldMetaReader;
        $this->docReader = $docReader;
    }

    /**
     * {@inheritdoc}
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\InterfaceType) {
            $typeName = $typeMeta->name;
            $result = [
                'name' => $typeName,
                'type' => 'graphql_interface',
                'fields' => []
            ];

            $interfaceTypeResolver = $this->getInterfaceTypeResolver($typeMeta);
            if (!empty($interfaceTypeResolver)) {
                $result['typeResolver'] = $interfaceTypeResolver;
            }

            $fields = $typeMeta->getFields();
            foreach ($fields as $fieldName => $fieldMeta) {
                $result['fields'][$fieldName] = $this->fieldMetaReader->read($fieldMeta);
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
     * Retrieve the interface type resolver if it exists from the meta data
     *
     * @param \GraphQL\Type\Definition\InterfaceType $interfaceTypeMeta
     * @return string
     */
    private function getInterfaceTypeResolver(\GraphQL\Type\Definition\InterfaceType $interfaceTypeMeta) : string
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $interfaceTypeMeta->astNode->directives;
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
