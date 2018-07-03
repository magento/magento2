<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

/**
 * Common cases for types that need extra formatting like wrapping or additional properties added to their definition
 */
class TypeMetaWrapperReader
{
    const ARGUMENT_PARAMETER = 'Argument';

    const OUTPUT_FIELD_PARAMETER = 'OutputField';

    const INPUT_FIELD_PARAMETER = 'InputField';

    /**
     * Read from type meta data and determine wrapping types that are needed and extra properties that need to be added
     *
     * @param \GraphQL\Type\Definition\Type $meta
     * @param string $parameterType Argument|OutputField|InputField
     * @return array
     */
    public function read(\GraphQL\Type\Definition\Type $meta, string $parameterType) : array
    {
        $result = [];
        if ($meta instanceof \GraphQL\Type\Definition\NonNull) {
            $result['required'] = true;
            $meta = $meta->getWrappedType();
        } else {
            $result['required'] = false;
        }
        if ($meta instanceof \GraphQL\Type\Definition\ListOfType) {
            $itemTypeMeta = $meta->ofType;
            if ($itemTypeMeta instanceof \GraphQL\Type\Definition\NonNull) {
                $result['itemsRequired'] = true;
                $itemTypeMeta = $itemTypeMeta->getWrappedType();
            } else {
                $result['itemsRequired'] = false;
            }
            $itemTypeName = $itemTypeMeta->name;
            $result['itemType'] = $itemTypeName;
            if ($itemTypeMeta instanceof \GraphQL\Type\Definition\ScalarType) {
                $result['type'] = 'ScalarArray' . $parameterType;
            } else {
                $result['type'] = 'ObjectArray' . $parameterType;
            }
        } else {
            $result['type'] = $meta->name;
        }

        return $result;
    }
}
