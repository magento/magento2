<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Collection;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\LocalizedException;

/**
 * Test SQL Injection Validator
 *
 * @coversDefaultClass \Magento\Framework\Data\Collection\SqlInjectionValidator
 */
class SqlInjectionValidatorTest extends TestCase
{
    /**
     * @var SqlInjectionValidator
     */
    private $validator;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->validator = new SqlInjectionValidator();
    }

    /**
     * Test that safe SQL patterns are allowed
     *
     * @dataProvider safeSqlProvider
     * @param string $sql
     */
    public function testSafeSqlPatternsAreAllowed(string $sql): void
    {
        $result = $this->validator->validate($sql);
        $this->assertTrue($result, "SQL should be considered safe: $sql");
    }

    /**
     * Test that dangerous SQL patterns are rejected
     *
     * @dataProvider dangerousSqlProvider
     * @param string $sql
     */
    public function testDangerousSqlPatternsAreRejected(string $sql): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/SQL injection|Suspicious SQL/');
        $this->validator->validate($sql);
    }

    /**
     * Test that dangerous SQL can be detected without throwing exception
     *
     * @dataProvider dangerousSqlProvider
     * @param string $sql
     */
    public function testDangerousSqlDetectionWithoutException(string $sql): void
    {
        $result = $this->validator->validate($sql, false);
        $this->assertFalse($result, "SQL should be considered dangerous: $sql");
    }

    /**
     * Test looksProperlyEscaped method
     */
    public function testLooksProperlyEscaped(): void
    {
        // Properly escaped patterns
        $this->assertTrue($this->validator->looksProperlyEscaped("`customer_id` = ?"));
        $this->assertTrue($this->validator->looksProperlyEscaped("email IN (?, ?, ?)"));
        $this->assertTrue($this->validator->looksProperlyEscaped("name LIKE ?"));
        $this->assertTrue($this->validator->looksProperlyEscaped("status IS NULL"));
        $this->assertTrue($this->validator->looksProperlyEscaped("price BETWEEN ? AND ?"));

        // Not properly escaped patterns
        $this->assertFalse($this->validator->looksProperlyEscaped("1=1"));
        $this->assertFalse($this->validator->looksProperlyEscaped("admin' OR '1'='1"));
        $this->assertFalse($this->validator->looksProperlyEscaped("UNION SELECT * FROM users"));
    }

    /**
     * Test empty string is safe
     */
    public function testEmptyStringIsSafe(): void
    {
        $result = $this->validator->validate('');
        $this->assertTrue($result);

        $result = $this->validator->validate('   ');
        $this->assertTrue($result);
    }

    /**
     * Provide safe SQL patterns (properly escaped)
     *
     * @return array
     */
    public function safeSqlProvider(): array
    {
        return [
            'Quoted field with placeholder' => ["`customer_id` = ?"],
            'Table.column with placeholder' => ["main_table.entity_id = ?"],
            'IN clause with placeholders' => ["status IN (?, ?, ?)"],
            'LIKE clause with placeholder' => ["`email` LIKE ?"],
            'IS NULL check' => ["`deleted_at` IS NULL"],
            'IS NOT NULL check' => ["created_at IS NOT NULL"],
            'BETWEEN with placeholders' => ["`price` BETWEEN ? AND ?"],
            'Quoted identifier with string' => ["`name` = 'test'"],
            'Multiple conditions with AND' => ["`status` = ? AND `type` = ?"],
            'Greater than comparison' => ["`qty` > ?"],
            'Less than or equal comparison' => ["price <= ?"],
            'Not equal comparison' => ["state != ?"],
            'Double quoted identifier' => ['"customer_id" = ?'],
            'Complex field name' => ["`main_table`.`customer_id` = ?"],
        ];
    }

    /**
     * Provide dangerous SQL patterns (injection attempts)
     *
     * @return array
     */
    public function dangerousSqlProvider(): array
    {
        return [
            // UNION-based injection
            'UNION SELECT attack' => ["' UNION SELECT * FROM admin_user WHERE '1'='1"],
            'UNION ALL SELECT' => ["1 UNION ALL SELECT password FROM users"],

            // Comment-based attacks
            'SQL line comment' => ["admin' --"],
            'MySQL hash comment' => ["test' #"],
            'Block comment attack' => ["value /* comment */ OR 1=1"],

            // Stacked queries
            'Stacked DELETE' => ["value'; DELETE FROM users; --"],
            'Stacked DROP' => ["'; DROP TABLE customers; --"],
            'Multiple statements' => ["test; SELECT * FROM admin"],

            // Subquery injection
            'Subquery in WHERE' => ["(SELECT password FROM admin_user WHERE user_id=1)"],
            'Nested SELECT' => ["1 AND (SELECT COUNT(*) FROM users) > 0"],

            // Information schema access
            'Information schema query' => ["' OR 1=1 UNION SELECT table_name FROM INFORMATION_SCHEMA.TABLES --"],
            'MySQL user table' => ["' OR 1=1 UNION SELECT user FROM MYSQL.USER --"],

            // Boolean-based blind injection
            'Boolean OR' => ["' OR 1=1 --"],
            'Boolean AND' => ["' AND 1=1 --"],

            // Time-based blind injection
            'SLEEP function' => ["' OR SLEEP(5) --"],
            'BENCHMARK function' => ["' OR BENCHMARK(1000000, MD5('test')) --"],
            'WAITFOR DELAY' => ["'; WAITFOR DELAY '00:00:05' --"],

            // Database modification
            'DROP TABLE' => ["'; DROP TABLE users; --"],
            'TRUNCATE TABLE' => ["'; TRUNCATE TABLE sessions; --"],
            'ALTER TABLE' => ["'; ALTER TABLE users ADD COLUMN hacked INT; --"],

            // File system access
            'LOAD_FILE function' => ["' UNION SELECT LOAD_FILE('/etc/passwd') --"],
            'INTO OUTFILE' => ["' INTO OUTFILE '/tmp/hacked.txt' --"],

            // EXEC/EXECUTE attacks
            'EXEC command' => ["'; EXEC sp_executesql N'DROP TABLE users'; --"],
            'EXECUTE command' => ["'; EXECUTE('DROP TABLE users'); --"],

            // CHAR-based injection
            'CHAR function' => ["admin' OR username=CHAR(97,100,109,105,110) --"],

            // Multiple separators
            'Double semicolon' => ["test;;"],

            // Hexadecimal injection
            'Hex string' => ["0x61646d696e"],

            // Unbalanced quotes
            'Unclosed single quote' => ["admin' OR '1'='1' AND 'x'='x"],
        ];
    }
}
