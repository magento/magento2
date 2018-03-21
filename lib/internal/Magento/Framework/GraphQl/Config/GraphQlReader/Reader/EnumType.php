<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\GraphQlReader\Reader;

use Magento\Framework\GraphQl\Config\GraphQlReader\TypeMetaReaderInterface;

class EnumType implements TypeMetaReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read(\GraphQL\Type\Definition\Type $typeMeta) : ?array
    {
        if ($typeMeta instanceof \GraphQL\Type\Definition\EnumType) {
            $result = [
                'name' => $typeMeta->name,
                'type' => 'graphql_enum',
                'items' => [] // Populated later
            ];
            foreach ($typeMeta->getValues() as $value) {
                $description = '';
                if (!empty($value->astNode->directives)) {
                    $description = $this->readTypeDescription($value);
                }

                // TODO: Simplify structure, currently name is lost during conversion to GraphQL schema
                $result['items'][$value->value] = [
                    'name' => strtolower($value->name),
                    '_value' => $value->value,
                    'description' => $description
                ];
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
                    if ($directiveArgument->name->value == 'description') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return '';
    }
}
