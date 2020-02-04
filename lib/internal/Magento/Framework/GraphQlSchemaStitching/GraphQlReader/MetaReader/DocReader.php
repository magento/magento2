<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

/**
 * Reads documentation from the annotation @doc of an AST node
 */
class DocReader
{
    /**
     * Read documentation annotation for a specific node if exists
     *
     * @param \GraphQL\Language\AST\NodeList $directives
     * @return string
     */
    public function read(\GraphQL\Language\AST\NodeList $directives) : string
    {
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
