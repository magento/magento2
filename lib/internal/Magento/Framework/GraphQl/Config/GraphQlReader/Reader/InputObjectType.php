<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\Reader;

use Magento\Framework\GraphQl\Config\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader\TypeMetaReader;

class InputObjectType implements TypeMetaReaderInterface
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
            // TODO arguments don't make sense here, but expected to be always present in \Magento\Framework\GraphQl\Config\Data\Mapper\TypeMapper::map
            'arguments' => []
        ];

        $result = array_merge($result, $this->typeMetaReader->readTypeMeta($typeMeta, 'InputField'));
        return $result;
    }
}
