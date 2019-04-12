<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

/**
 * Reads documentation from the annotation @cacheable of an AST node
 */
class CacheTagReader
{
    /**
     * Read documentation annotation for a specific node if exists
     *
     * @param \GraphQL\Language\AST\NodeList $directives
     * @return array
     */
    public function read(\GraphQL\Language\AST\NodeList $directives) : array
    {
        $argMap = [];
        foreach ($directives as $directive) {
            if ($directive->name->value == 'cache') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'cache_tag') {
                        $argMap = array_merge(
                            $argMap,
                            ["cache_tag" => $directiveArgument->value->value]
                        );
                    }
                    if ($directiveArgument->name->value == 'cacheable') {
                        $argMap = array_merge(
                            $argMap,
                            ["cacheable" => $directiveArgument->value->value]
                        );
                    }
                    if ($directiveArgument->name->value == 'cacheIdentityResolver') {
                        $argMap = array_merge(
                            $argMap,
                            ["cacheIdentityResolver" => $directiveArgument->value->value]
                        );
                    }
                }
            }
        }
        return $argMap;
    }
}
