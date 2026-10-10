<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\Parser;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AttributesJoinerTest extends TestCase
{
    /**
     * @param string $query
     * @param string[] $expected
     */
    #[DataProvider('queryFieldsDataProvider')]
    public function testGetQueryFields(string $query, array $expected): void
    {
        $document = Parser::parse($query);
        $fragments = [];
        $fieldNode = null;
        foreach ($document->definitions as $definition) {
            if ($definition instanceof FragmentDefinitionNode) {
                $fragments[$definition->name->value] = $definition;
            } elseif ($fieldNode === null) {
                $fieldNode = $definition->selectionSet->selections[0];
            }
        }
        $this->assertInstanceOf(FieldNode::class, $fieldNode);
        $this->assertSame(NodeKind::FIELD, $fieldNode->kind);

        $resolveInfo = (new \ReflectionClass(ResolveInfo::class))->newInstanceWithoutConstructor();
        $resolveInfo->fragments = $fragments;

        $actual = (new AttributesJoiner())->getQueryFields($fieldNode, $resolveInfo);
        sort($actual);
        sort($expected);
        $this->assertSame($expected, array_values(array_unique($actual)));
    }

    /**
     * @return array
     */
    public static function queryFieldsDataProvider(): array
    {
        return [
            'single fragment spread' => [
                'query { categoryList { ...a } } fragment a on CategoryTree { name uid }',
                ['name', 'uid']
            ],
            'spread nested in spread' => [
                'query { categoryList { ...a children { uid } } } '
                . 'fragment a on CategoryTree { ...b } fragment b on CategoryTree { ...c name } '
                . 'fragment c on CategoryTree { name uid }',
                ['children', 'name', 'uid']
            ],
            'inline fragment nested in spread' => [
                'query { categoryList { ...a } } '
                . 'fragment a on CategoryTree { ... on CategoryTree { url_key } }',
                ['url_key']
            ],
        ];
    }
}
