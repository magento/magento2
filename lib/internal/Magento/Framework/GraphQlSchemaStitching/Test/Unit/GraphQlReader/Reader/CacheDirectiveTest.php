<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\Test\Unit\GraphQlReader\Reader;

use GraphQL\Utils\BuildSchema;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\CacheAnnotationReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DocReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\FieldMetaReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\TypeMetaWrapperReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader\InputObjectType;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader\InterfaceType;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\TypeMetaReaderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Checks that input and interface type readers read the @doc and @cache directives independently
 */
class CacheDirectiveTest extends TestCase
{
    private const SCHEMA = <<<'GRAPHQL'
directive @doc(description: String) on INPUT_OBJECT | INTERFACE
directive @cache(cacheable: Boolean, cacheIdentity: String) on INPUT_OBJECT | INTERFACE

type Query {
    item(filter: TestInput): TestInterface
}

input TestInput %s {
    name: String
}

interface TestInterface %s {
    name: String
}
GRAPHQL;

    /**
     * @param string $directives
     * @param array $expected
     */
    #[DataProvider('directivesDataProvider')]
    public function testInputObjectTypeReadsDocAndCacheIndependently(string $directives, array $expected): void
    {
        $typeMetaReader = $this->createStub(TypeMetaWrapperReader::class);
        $typeMetaReader->method('read')->willReturn(['type' => 'String']);
        $reader = new InputObjectType($typeMetaReader, new DocReader(), new CacheAnnotationReader());

        $this->assertMeta($expected, $reader, sprintf(self::SCHEMA, $directives, ''), 'TestInput');
    }

    /**
     * @param string $directives
     * @param array $expected
     */
    #[DataProvider('directivesDataProvider')]
    public function testInterfaceTypeReadsDocAndCacheIndependently(string $directives, array $expected): void
    {
        $fieldMetaReader = $this->createStub(FieldMetaReader::class);
        $fieldMetaReader->method('read')->willReturn([]);
        $reader = new InterfaceType($fieldMetaReader, new DocReader(), new CacheAnnotationReader());

        $this->assertMeta($expected, $reader, sprintf(self::SCHEMA, '', $directives), 'TestInterface');
    }

    /**
     * @return array
     */
    public static function directivesDataProvider(): array
    {
        return [
            'neither directive' => ['', []],
            'doc only' => ['@doc(description: "Test type")', ['description' => 'Test type']],
            'cache only' => [
                '@cache(cacheIdentity: "TestIdentity")',
                ['cache' => ['cacheIdentity' => 'TestIdentity']],
            ],
            'doc and cache' => [
                '@doc(description: "Test type") @cache(cacheIdentity: "TestIdentity")',
                ['description' => 'Test type', 'cache' => ['cacheIdentity' => 'TestIdentity']],
            ],
            'not cacheable' => ['@cache(cacheable: false)', ['cache' => ['cacheable' => false]]],
        ];
    }

    /**
     * Assert the description and cache metadata read for a type of the schema
     *
     * @param array $expected
     * @param TypeMetaReaderInterface $reader
     * @param string $schema
     * @param string $typeName
     * @return void
     */
    private function assertMeta(
        array $expected,
        TypeMetaReaderInterface $reader,
        string $schema,
        string $typeName
    ): void {
        $result = $reader->read(BuildSchema::build($schema)->getType($typeName));

        $this->assertSame($typeName, $result['name']);
        $this->assertSame($expected, array_intersect_key($result, array_flip(['description', 'cache'])));
    }
}
