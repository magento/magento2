<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\Test\Unit\GraphQlReader\MetaReader;

use GraphQL\Language\AST\NodeList;
use GraphQL\Utils\BuildSchema;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DirectiveReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test the generic custom directive reader
 */
class DirectiveReaderTest extends TestCase
{
    private const SCHEMA = <<<'GRAPHQL'
type Query {
    noDirective: String
    single: String @resolver(class: "Foo\\Bar") @myDirective(name: "first", enabled: true, limit: 5)
    repeated: String @myDirective(name: "first") @myDirective(name: "second", tags: ["a", "b"])
    withoutArguments: String @myDirective
}
GRAPHQL;

    /**
     * @param string $fieldName
     * @param array $expected
     */
    #[DataProvider('readDataProvider')]
    public function testRead(string $fieldName, array $expected): void
    {
        $reader = new DirectiveReader('myDirective');

        $this->assertSame($expected, $reader->read($this->getFieldDirectives($fieldName)));
    }

    /**
     * @return array
     */
    public static function readDataProvider(): array
    {
        return [
            'directive absent' => ['noDirective', []],
            'single occurrence, other directives ignored' => [
                'single',
                [['name' => 'first', 'enabled' => true, 'limit' => 5]],
            ],
            'repeated directive' => [
                'repeated',
                [['name' => 'first'], ['name' => 'second', 'tags' => ['a', 'b']]],
            ],
            'directive without arguments' => ['withoutArguments', [[]]],
        ];
    }

    /**
     * Get the AST directives of a Query field
     *
     * @param string $fieldName
     * @return NodeList
     */
    private function getFieldDirectives(string $fieldName): NodeList
    {
        $schema = BuildSchema::build(self::SCHEMA, null, ['assumeValid' => true, 'assumeValidSDL' => true]);

        return $schema->getQueryType()->getField($fieldName)->astNode->directives;
    }
}
