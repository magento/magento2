<?php

/*
 * This file is part of the JSON Lint package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use Seld\JsonLint\DuplicateKeyException;

class JsonParserTest extends PHPUnit_Framework_TestCase
{
    protected $json = array(
        '42', '42.3', '0.3', '-42', '-42.3', '-0.3',
        '2e1', '2E1', '-2e1', '-2E1', '2E+2', '2E-2', '-2E+2', '-2E-2',
        'true', 'false', 'null', '""', '[]', '{}', '"string"',
        '["a", "sdfsd"]',
        '{"foo":"bar", "bar":"baz", "":"buz"}',
        '{"":"foo", "_empty_":"bar"}',
        '"\u00c9v\u00e9nement"',
        '"http:\/\/foo.com"',
        '"zo\\\\mg"',
        '{"test":"\u00c9v\u00e9nement"}',
        '["\u00c9v\u00e9nement"]',
        '"foo/bar"',
        '{"test":"http:\/\/foo\\\\zomg"}',
        '["http:\/\/foo\\\\zomg"]',
        '{"":"foo"}',
        '{"a":"b", "b":"c"}',
    );

    /**
     * @dataProvider provideValidStrings
     */
    public function testParsesValidStrings($input)
    {
        $parser = new JsonParser();
        $this->assertEquals(json_decode($input), $parser->parse($input));
    }

    public function provideValidStrings()
    {
        $strings = array();
        foreach ($this->json as $input) {
            $strings[] = array($input);
        }

        return $strings;
    }

    public function testErrorOnTrailingComma()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{
    "foo":"bar",
}');
            $this->fail('Invalid trailing comma should be detected');
        } catch (ParsingException $e) {
            $this->assertContains('It appears you have an extra trailing comma', $e->getMessage());
        }
    }

    public function testErrorOnInvalidQuotes()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{
    "foo": \'bar\',
}');
            $this->fail('Invalid quotes for string should be detected');
        } catch (ParsingException $e) {
            $this->assertContains('Invalid string, it appears you used single quotes instead of double quotes', $e->getMessage());
        }
    }

    public function testErrorOnUnescapedBackslash()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{
    "foo": "bar\z",
}');
            $this->fail('Invalid unescaped string should be detected');
        } catch (ParsingException $e) {
            $this->assertContains('Invalid string, it appears you have an unescaped backslash at: \z', $e->getMessage());
        }
    }

    public function testErrorOnUnterminatedString()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{"bar": "foo}');
            $this->fail('Invalid unterminated string should be detected');
        } catch (ParsingException $e) {
            $this->assertContains('Invalid string, it appears you forgot to terminated the string, or attempted to write a multiline string which is invalid', $e->getMessage());
        }
    }

    public function testErrorOnMultilineString()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{"bar": "foo
bar"}');
            $this->fail('Invalid multi-line string should be detected');
        } catch (ParsingException $e) {
            $this->assertContains('Invalid string, it appears you forgot to terminated the string, or attempted to write a multiline string which is invalid', $e->getMessage());
        }
    }

    public function testErrorAtBeginning()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('

');
            $this->fail('Empty string should be invalid');
        } catch (ParsingException $e) {
            $this->assertContains("Parse error on line 1:\n\n^", $e->getMessage());
        }
    }

    public function testParsesMultiInARow()
    {
        $parser = new JsonParser();
        foreach ($this->json as $input) {
            $this->assertEquals(json_decode($input), $parser->parse($input));
        }
    }

    public function testDetectsKeyOverrides()
    {
        $parser = new JsonParser();

        try {
            $parser->parse('{"a":"b", "a":"c"}', JsonParser::DETECT_KEY_CONFLICTS);
            $this->fail('Duplicate keys should not be allowed');
        } catch (DuplicateKeyException $e) {
            $this->assertContains('Duplicate key: a', $e->getMessage());
            $this->assertSame('a', $e->getKey());
            $this->assertSame(array('line' => 1, 'key' => 'a'), $e->getDetails());
        }
    }

    public function testDetectsKeyOverridesWithEmpty()
    {
        $parser = new JsonParser();

        try {
            $parser->parse('{"":"b", "_empty_":"a"}', JsonParser::DETECT_KEY_CONFLICTS);
            $this->fail('Duplicate keys should not be allowed');
        } catch (DuplicateKeyException $e) {
            $this->assertContains('Duplicate key: _empty_', $e->getMessage());
            $this->assertSame('_empty_', $e->getKey());
            $this->assertSame(array('line' => 1, 'key' => '_empty_'), $e->getDetails());
        }
    }

    public function testDuplicateKeys()
    {
        $parser = new JsonParser();

        $result = $parser->parse('{"a":"b", "a":"c", "a":"d"}', JsonParser::ALLOW_DUPLICATE_KEYS);
        $this->assertThat($result,
            $this->logicalAnd(
                $this->objectHasAttribute('a'),
                $this->objectHasAttribute('a.1'),
                $this->objectHasAttribute('a.2')
            )
        );
    }

    public function testDuplicateKeysWithEmpty()
    {
        $parser = new JsonParser();

        $result = $parser->parse('{"":"a", "_empty_":"b"}', JsonParser::ALLOW_DUPLICATE_KEYS);
        $this->assertThat($result,
            $this->logicalAnd(
                $this->objectHasAttribute('_empty_'),
                $this->objectHasAttribute('_empty_.1')
            )
        );
    }

    public function testParseToArray()
    {
        $parser = new JsonParser();

        $json = '{"one":"a", "two":{"three": "four"}, "": "empty"}';
        $result = $parser->parse($json, JsonParser::PARSE_TO_ASSOC);
        $this->assertSame(json_decode($json, true), $result);
    }

    public function testFileWithBOM()
    {
        try {
            $parser = new JsonParser();
            $parser->parse(file_get_contents(dirname(__FILE__) .'/bom.json'));
            $this->fail('BOM should be detected');
        } catch (ParsingException $e) {
            $this->assertContains('BOM detected', $e->getMessage());
        }
    }
}
