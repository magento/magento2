<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching\Test\Unit\GraphQlReader\MetaReader;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Utils\BuildSchema;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\CacheAnnotationReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DeprecatedAnnotationReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DirectiveReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\DocReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\FieldMetaReader;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\MetaReader\TypeMetaWrapperReader;
use PHPUnit\Framework\TestCase;

/**
 * Test the reading of custom directives by FieldMetaReader
 */
class FieldMetaReaderTest extends TestCase
{
    private const SCHEMA = <<<'GRAPHQL'
type Query {
    plain: String @doc(description: "Plain field")
    protected: String @doc(description: "Protected field") @myDirective(name: "first") @myDirective(name: "second")
}
GRAPHQL;

    public function testReadWithoutDirectiveReaders(): void
    {
        $result = $this->createReader()->read($this->getField('protected'));

        $this->assertArrayNotHasKey('directives', $result);
        $this->assertSame('Protected field', $result['description']);
    }

    public function testReadExposesCustomDirectives(): void
    {
        $reader = $this->createReader(['my_directive' => new DirectiveReader('myDirective')]);

        $result = $reader->read($this->getField('protected'));

        $this->assertSame(
            ['my_directive' => [['name' => 'first'], ['name' => 'second']]],
            $result['directives']
        );
        $this->assertSame('Protected field', $result['description']);
    }

    public function testReadSkipsAbsentDirectives(): void
    {
        $reader = $this->createReader(['my_directive' => new DirectiveReader('myDirective')]);

        $result = $reader->read($this->getField('plain'));

        $this->assertArrayNotHasKey('directives', $result);
    }

    public function testConstructorRejectsInvalidDirectiveReader(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('GraphQL directive reader "invalid" must implement');

        $this->createReader(['invalid' => new \stdClass()]);
    }

    /**
     * @param array $directiveReaders
     * @return FieldMetaReader
     */
    private function createReader(array $directiveReaders = []): FieldMetaReader
    {
        return new FieldMetaReader(
            new TypeMetaWrapperReader(),
            new DocReader(),
            new CacheAnnotationReader(),
            new DeprecatedAnnotationReader(),
            $directiveReaders
        );
    }

    /**
     * @param string $fieldName
     * @return FieldDefinition
     */
    private function getField(string $fieldName): FieldDefinition
    {
        $schema = BuildSchema::build(self::SCHEMA, null, ['assumeValid' => true, 'assumeValidSDL' => true]);

        return $schema->getQueryType()->getField($fieldName);
    }
}
