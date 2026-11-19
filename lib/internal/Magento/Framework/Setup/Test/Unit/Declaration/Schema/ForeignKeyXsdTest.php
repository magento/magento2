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
 * Validate declarative foreign key delete actions.
 */
class ForeignKeyXsdTest extends TestCase
{
    /**
     * Verify that only supported delete actions pass XML validation.
     *
     * @param string $action
     * @param bool $valid
     * @return void
     */
    #[DataProvider('deleteActionProvider')]
    public function testDeleteAction(string $action, bool $valid): void
    {
        $xml = '<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<table name="child"><column xsi:type="int" name="parent_id"/>'
            . '<constraint xsi:type="foreign" referenceId="CHILD_PARENT" table="child" column="parent_id"'
            . ' referenceTable="parent" referenceColumn="id" onDelete="' . $action . '"/>'
            . '</table></schema>';
        $schema = (new UrnResolver())->getRealPath(
            'urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd'
        );
        $errors = (new XsdValidator())->validate($schema, $xml);

        self::assertSame($valid, empty($errors), implode("\n", $errors));
    }

    /**
     * Supported and unsupported delete actions.
     *
     * @return array
     */
    public static function deleteActionProvider(): array
    {
        return [
            'restrict' => ['RESTRICT', true],
            'no action' => ['NO ACTION', true],
            'cascade' => ['CASCADE', true],
            'set null' => ['SET NULL', true],
            'unsupported' => ['SET DEFAULT', false],
            'invalid' => ['INVALID', false],
            'lowercase' => ['restrict', false],
        ];
    }
}
