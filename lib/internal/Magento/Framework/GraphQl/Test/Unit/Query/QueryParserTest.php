<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\QueryParser;
use PHPUnit\Framework\TestCase;

class QueryParserTest extends TestCase
{
    public function testParseValidQueryWithinDepthLimit(): void
    {
        $parser = new QueryParser(maxNestingDepth: 200);
        $query = '{ products { items { name sku } } }';
        $result = $parser->parse($query);
        $this->assertNotNull($result);
    }

    public function testParseRejectsDeeplyNestedInlineFragments(): void
    {
        $depth = 250;
        $parser = new QueryParser(maxNestingDepth: 200);

        $query = str_repeat('{ ... on Query ', $depth) . '{ __typename }' . str_repeat(' }', $depth);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Query is too deep');
        $parser->parse($query);
    }

    public function testParseRejectsDeeplyNestedSelectionSets(): void
    {
        $depth = 250;
        $parser = new QueryParser(maxNestingDepth: 200);

        $query = str_repeat('{ a ', $depth) . '{ name }' . str_repeat(' }', $depth);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Query is too deep');
        $parser->parse($query);
    }

    public function testParseAllowsQueryAtExactDepthLimit(): void
    {
        $parser = new QueryParser(maxNestingDepth: 5);
        $query = '{ a { b { c { d { e } } } } }';
        $result = $parser->parse($query);
        $this->assertNotNull($result);
    }

    public function testParseRejectsQueryExceedingDepthByOne(): void
    {
        $parser = new QueryParser(maxNestingDepth: 5);
        $query = '{ a { b { c { d { e { f } } } } } }';

        $this->expectException(GraphQlInputException::class);
        $parser->parse($query);
    }

    public function testBracesInsideStringsAreIgnored(): void
    {
        $parser = new QueryParser(maxNestingDepth: 5);
        $query = '{ products(filter: { name: { eq: "{{{{{{{{{{" } }) { items { name } } }';
        $result = $parser->parse($query);
        $this->assertNotNull($result);
    }

    public function testParseCachesResults(): void
    {
        $parser = new QueryParser(maxNestingDepth: 200);
        $query = '{ products { items { name } } }';

        $result1 = $parser->parse($query);
        $result2 = $parser->parse($query);

        $this->assertSame($result1, $result2);
    }

    public function testMassivelyNestedQueryDoesNotCrash(): void
    {
        $depth = 3000;
        $parser = new QueryParser(maxNestingDepth: 200);

        $query = str_repeat('{ ... on Query ', $depth) . '{ __typename }' . str_repeat(' }', $depth);

        $this->expectException(GraphQlInputException::class);
        $parser->parse($query);
    }

    public function testDefaultDepthAllowsLegitimateComplexQueries(): void
    {
        $parser = new QueryParser();
        $query = '{ category(id: 2) { products { items { categories { products { items { '
            . 'categories { products { items { name } } } } } } } } } }';
        $result = $parser->parse($query);
        $this->assertNotNull($result);
    }

    public function testParseRejectsDeeplyNestedInputObjects(): void
    {
        $depth = 250;
        $parser = new QueryParser(maxNestingDepth: 200);

        $query = '{ products(filter: ' . str_repeat('{ a: ', $depth)
            . '"x"' . str_repeat(' }', $depth) . ') { items { name } } }';

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Query is too deep');
        $parser->parse($query);
    }

    public function testParseRejectsDeeplyNestedListValues(): void
    {
        $depth = 250;
        $parser = new QueryParser(maxNestingDepth: 200);

        $query = '{ products(ids: ' . str_repeat('[', $depth)
            . '1' . str_repeat(']', $depth) . ') { items { name } } }';

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Query is too deep');
        $parser->parse($query);
    }

    public function testBracketsInsideStringsAreIgnored(): void
    {
        $parser = new QueryParser(maxNestingDepth: 5);
        $query = '{ products(filter: { name: { eq: "[[[[[[[[[[" } }) { items { name } } }';
        $result = $parser->parse($query);
        $this->assertNotNull($result);
    }

    public function testMixedBracesAndBracketsCountTogether(): void
    {
        $parser = new QueryParser(maxNestingDepth: 5);
        $query = '{ a(x: [{ b: [{ c: 1 }] }]) { name } }';
        $result = $parser->parse($query);
        $this->assertNotNull($result);
    }

    public function testMixedNestingExceedingLimit(): void
    {
        $parser = new QueryParser(maxNestingDepth: 4);
        $query = '{ a(x: [{ b: [{ c: 1 }] }]) { name } }';

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Query is too deep');
        $parser->parse($query);
    }
}
