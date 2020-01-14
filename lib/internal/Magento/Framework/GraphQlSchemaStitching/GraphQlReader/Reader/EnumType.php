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
class EnumType implements TypeMetaReaderInterface
{
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
     * @inheritdoc
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\EnumType) {
            $result = [
                'name' => $typeMeta->name,
                'type' => 'graphql_enum',
                'items' => [] // Populated later
            ];
            foreach ($typeMeta->getValues() as $enumValueMeta) {
                $result['items'][$enumValueMeta->value] = [
                    'name' => strtolower($enumValueMeta->name),
                    '_value' => $enumValueMeta->value,
                    'description' => $enumValueMeta->description,
                    'deprecationReason' =>$enumValueMeta->deprecationReason
                ];

                if ($this->docReader->read($enumValueMeta->astNode->directives)) {
                    $result['items'][$enumValueMeta->value]['description'] =
                        $this->docReader->read($enumValueMeta->astNode->directives);
                }
            }

            if ($this->docReader->read($typeMeta->astNode->directives)) {
                $result['description'] = $this->docReader->read($typeMeta->astNode->directives);
            }

            return $result;

        } else {
            return [];
        }
    }
}
