<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\Reader;

use Magento\Framework\GraphQl\Config\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader\TypeMetaReader;
use Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader\DocReader;

class InputObjectType implements TypeMetaReaderInterface
{
    /**
     * @var TypeMetaReader
     */
    private $typeMetaReader;

    /**
     * @var DocReader
     */
    private $docReader;

    /**
     * @param TypeMetaReader $typeMetaReader
     * @param DocReader $docReader
     */
    public function __construct(TypeMetaReader $typeMetaReader, DocReader $docReader)
    {
        $this->typeMetaReader = $typeMetaReader;
        $this->docReader = $docReader;
    }

    /**
     * {@inheritdoc}
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : ?array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\InputObjectType) {
            $typeName = $typeMeta->name;
            $result = [
                'name' => $typeName,
                'type' => 'graphql_input',
                'fields' => [] // Populated later
            ];
            $fields = $typeMeta->getFields();
            foreach ($fields as $fieldName => $fieldMeta) {
                $result['fields'][$fieldName] = $this->readInputObjectFieldMeta($fieldMeta);
            }

            if ($this->docReader->readTypeDescription($typeMeta->astNode->directives)) {
                $result['description'] = $this->docReader->readTypeDescription($typeMeta->astNode->directives);
            }
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @param \GraphQL\Type\Definition\InputObjectField $fieldMeta
     * @return array
     */
    private function readInputObjectFieldMeta(\GraphQL\Type\Definition\InputObjectField $fieldMeta) : array
    {
        $fieldName = $fieldMeta->name;
        $typeMeta = $fieldMeta->getType();
        $result = [
            'name' => $fieldName,
            'required' => false,
            'arguments' => []
        ];

        $result = array_merge($result, $this->typeMetaReader->readTypeMeta($typeMeta, 'InputField'));

        if ($this->docReader->readTypeDescription($fieldMeta->astNode->directives)) {
                $result['description'] = $this->docReader->readTypeDescription($fieldMeta->astNode->directives);
        }

        return $result;
    }
}
