<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

/**
 * Reads interfaces implementations from the annotation @implements of an AST node
 */
class ImplementsReader
{
    /**
     * Read implements annotation for a specific node if exists
     *
     * @param \GraphQL\Language\AST\NodeList $directives
     * @return string[]|null
     */
    public function read(\GraphQL\Language\AST\NodeList $directives) : array
    {
        foreach ($directives as $directive) {
            if ($directive->name->value == 'implements') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'interfaces') {
                        if ($directiveArgument->value->kind == 'ListValue') {
                            $interfacesNames = [];
                            foreach ($directiveArgument->value->values as $stringValue) {
                                $interfacesNames[] = $stringValue->value;
                            }
                            return $interfacesNames;
                        } else {
                            return [$directiveArgument->value->value];
                        }
                    }
                }
            }
        }
        return [];
    }
}
