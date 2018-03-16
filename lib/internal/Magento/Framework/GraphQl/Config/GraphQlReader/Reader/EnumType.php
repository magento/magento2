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
                // TODO: Simplify structure, currently name is lost during conversion to GraphQL schema
                $result['items'][$value->value] = [
                    'name' => strtolower($value->name),
                    '_value' => $value->value
                ];
            }

            return $result;
        } else {
            return null;
        }
    }
}
