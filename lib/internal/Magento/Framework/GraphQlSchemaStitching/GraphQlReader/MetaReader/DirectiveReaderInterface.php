<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

use GraphQL\Language\AST\NodeList;

/**
 * Reads a custom directive from the directives of an AST node.
 *
 * Implementations are registered in the "directiveReaders" argument of FieldMetaReader, the key being
 * the name under which the result is exposed by Field::getDirectives().
 *
 * @api
 */
interface DirectiveReaderInterface
{
    /**
     * Read the directive from the given directives
     *
     * The result must be serializable as it is stored in the GraphQL config cache.
     *
     * @param NodeList $directives
     * @return array Empty array when the directive is not present
     */
    public function read(NodeList $directives): array;
}
