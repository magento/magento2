<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\Reader;

use Magento\Framework\GraphQl\Config\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader\FieldMetaReader;

class InterfaceType implements TypeMetaReaderInterface
{
    /**
     * @var FieldMetaReader
     */
    private $fieldMetaReader;

    /**
     * @param FieldMetaReader $fieldMetaReader
     */
    public function __construct(FieldMetaReader $fieldMetaReader)
    {
        $this->fieldMetaReader = $fieldMetaReader;
    }

    /**
     * @param \GraphQL\Type\Definition\Type $typeMeta
     * @return array
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : ?array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\InterfaceType) {
            $typeName = $typeMeta->name;
            $result = [
                'name' => $typeName,
                'type' => 'graphql_interface',
                'fields' => []
            ];

            $interfaceTypeResolver = $this->getInterfaceTypeResolver($typeMeta);
            if ($interfaceTypeResolver) {
                $result['typeResolver'] = $interfaceTypeResolver;
            }

            $fields = $typeMeta->getFields();
            foreach ($fields as $fieldName => $fieldMeta) {
                $result['fields'][$fieldName] = $this->fieldMetaReader->readFieldMeta($fieldMeta);
            }

            if (!empty($typeMeta->astNode->directives) && !($typeMeta instanceof \GraphQL\Type\Definition\ScalarType)) {
                $description = $this->readTypeDescription($typeMeta);
                if ($description) {
                    $result['description'] = $description;
                }
            }

            return $result;
        } else {
            return null;
        }
    }

    /**
     * Retrieve the interface type resolver if it exists from the meta data
     *
     * @param \GraphQL\Type\Definition\InterfaceType $interfaceTypeMeta
     * @return string|null
     */
    private function getInterfaceTypeResolver(\GraphQL\Type\Definition\InterfaceType $interfaceTypeMeta) : ?string
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
                    if ($directiveArgument->name->value == 'description' && $directiveArgument->value->value) {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return '';
    }
}
