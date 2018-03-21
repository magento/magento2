<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\Reader;

use Magento\Framework\GraphQl\Config\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader\FieldMetaReader;

class ObjectType implements TypeMetaReaderInterface
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
     * {@inheritdoc}
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : ?array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\ObjectType) {
            $typeName = $typeMeta->name;
            $result = [
                'name' => $typeName,
                'type' => 'graphql_type',
                'fields' => [], // Populated later
            ];

            $interfaces = $typeMeta->getInterfaces();
            foreach ($interfaces as $interfaceMeta) {
                $interfaceName = $interfaceMeta->name;
                $result['implements'][$interfaceName] = [
                    'interface' => $interfaceName,
                    'copyFields' => true // TODO: Configure in separate config
                ];
            }

            $fields = $typeMeta->getFields();
            foreach ($fields as $fieldName => $fieldMeta) {
                $result['fields'][$fieldName] = $this->fieldMetaReader->readFieldMeta($fieldMeta);
            }

            if (!empty($typeMeta->astNode->directives) && !($typeMeta instanceof \GraphQL\Type\Definition\ScalarType)) {
                $result['description'] = $this->readTypeDescription($typeMeta);
            }

            return $result;
        } else {
            return null;
        }
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
