<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\Reader;

use Magento\Framework\GraphQl\Config\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader\FieldMetaReader;
use Magento\Framework\GraphQl\Config\GraphQlReader\MetaReader\DocReader;

class ObjectType implements TypeMetaReaderInterface
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

            if ($this->docReader->readTypeDescription($typeMeta->astNode->directives)) {
                    $result['description'] = $this->docReader->readTypeDescription($typeMeta->astNode->directives);
            }

            return $result;
        } else {
            return null;
        }
    }
}
