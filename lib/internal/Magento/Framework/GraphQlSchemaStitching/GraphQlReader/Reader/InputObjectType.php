<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader;

use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\TypeMetaReaderInterface;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\TypeMetaWrapperReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DocReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\CacheAnnotationReader;

/**
 * Composite configuration reader to handle the input object type meta
 */
class InputObjectType implements TypeMetaReaderInterface
{
    public const GRAPHQL_INPUT = 'graphql_input';

    /**
     * @var TypeMetaWrapperReader
     */
    private $typeMetaReader;

    /**
     * @var DocReader
     */
    private $docReader;

    /**
     * @var CacheAnnotationReader
     */
    private $cacheAnnotationReader;

    /**
     * @param TypeMetaWrapperReader $typeMetaReader
     * @param DocReader $docReader
     * @param CacheAnnotationReader|null $cacheAnnotationReader
     */
    public function __construct(
        TypeMetaWrapperReader $typeMetaReader,
        DocReader $docReader,
        ?CacheAnnotationReader $cacheAnnotationReader = null
    ) {
        $this->typeMetaReader = $typeMetaReader;
        $this->docReader = $docReader;
        $this->cacheAnnotationReader = $cacheAnnotationReader ?? \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CacheAnnotationReader::class);
    }

    /**
     * @inheritDoc
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\InputObjectType) {
            $typeName = $typeMeta->name;
            $result = [
                'name' => $typeName,
                'type' => self::GRAPHQL_INPUT,
                'fields' => [] // Populated later
            ];
            $fields = $typeMeta->getFields();
            foreach ($fields as $fieldName => $fieldMeta) {
                $result['fields'][$fieldName] = $this->readInputObjectFieldMeta($fieldMeta);
            }

            if ($this->docReader->read($typeMeta->astNode->directives)) {
                $result['description'] = $this->docReader->read($typeMeta->astNode->directives);
            }

            if ($this->docReader->read($typeMeta->astNode->directives)) {
                $result['cache'] = $this->cacheAnnotationReader->read($typeMeta->astNode->directives);
            }
            return $result;
        } else {
            return [];
        }
    }

    /**
     * Read the input's meta data
     *
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

        $result = array_merge(
            $result,
            $this->typeMetaReader->read($typeMeta, TypeMetaWrapperReader::INPUT_FIELD_PARAMETER)
        );

        if ($this->docReader->read($fieldMeta->astNode->directives)) {
                $result['description'] = $this->docReader->read($fieldMeta->astNode->directives);
        }

        return $result;
    }
}
