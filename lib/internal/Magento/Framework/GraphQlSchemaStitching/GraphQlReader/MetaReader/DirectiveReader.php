<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Utils\AST;

/**
 * Generic reader returning the arguments of every occurrence of a directive.
 *
 * Meant to be declared as a virtual type, so that a module can expose a custom directive with di.xml only:
 *
 * <virtualType name="Vendor\Module\GraphQl\MyDirectiveReader"
 *              type="Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DirectiveReader">
 *     <arguments>
 *         <argument name="directiveName" xsi:type="string">myDirective</argument>
 *     </arguments>
 * </virtualType>
 *
 * Result format: one entry per occurrence (repeatable directives are supported), each entry being the map of
 * the arguments of the occurrence, e.g. [['name' => 'foo'], ['name' => 'bar']].
 */
class DirectiveReader implements DirectiveReaderInterface
{
    /**
     * @var string
     */
    private $directiveName;

    /**
     * @param string $directiveName
     */
    public function __construct(string $directiveName)
    {
        $this->directiveName = $directiveName;
    }

    /**
     * @inheritdoc
     */
    public function read(NodeList $directives): array
    {
        $occurrences = [];
        /** @var DirectiveNode $directive */
        foreach ($directives as $directive) {
            if ($directive->name->value !== $this->directiveName) {
                continue;
            }
            $arguments = [];
            foreach ($directive->arguments as $argument) {
                $arguments[$argument->name->value] = AST::valueFromASTUntyped($argument->value);
            }
            $occurrences[] = $arguments;
        }

        return $occurrences;
    }
}
