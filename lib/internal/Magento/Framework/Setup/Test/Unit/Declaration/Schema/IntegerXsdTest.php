<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\TestFramework\Unit\Utility\XsdValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Validate integer defaults against the declarative schema.
 */
class IntegerXsdTest extends TestCase
{
    /**
     * Check the default restriction, including inherited integer types.
     */
    #[DataProvider('defaultDataProvider')]
    public function testDefaultValidation(string $type, string $default, bool $valid, string $unsigned): void
    {
        $xml = sprintf(
            '<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<table name="integer_defaults" resource="default" engine="innodb">'
            . '<column xsi:type="%s" name="value" unsigned="%s" nullable="true"'
            . ' identity="false" default="%s" comment="Value"/>'
            . '</table></schema>',
            $type,
            $unsigned,
            $default
        );
        $schema = (new UrnResolver())->getRealPath(
            'urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd'
        );
        $errors = (new XsdValidator())->validate($schema, $xml);

        self::assertSame($valid, $errors === [], implode(PHP_EOL, $errors));
    }

    /**
     * Provide valid and invalid defaults for all integer types sharing the restriction.
     */
    public static function defaultDataProvider(): array
    {
        $defaults = [
            'negative' => ['-1', true],
            'zero' => ['0', true],
            'positive' => ['1', true],
            'null' => ['null', true],
            'empty' => ['', false],
            'text' => ['bad', false],
            'decimal' => ['-1.5', false],
            'double minus' => ['--1', false],
            'minus only' => ['-', false],
            'trailing text' => ['1bad', false],
            'negative null' => ['-null', false],
            'non-ASCII digit' => ["\u{0661}", false],
        ];
        $data = [];
        foreach (['int', 'smallint', 'bigint', 'tinyint'] as $type) {
            foreach ($defaults as $label => [$default, $valid]) {
                $data[$type . ' ' . $label] = [$type, $default, $valid, 'false'];
            }
        }

        $data['unsigned negative'] = ['int', '-1', true, 'true'];

        return $data;
    }
}
