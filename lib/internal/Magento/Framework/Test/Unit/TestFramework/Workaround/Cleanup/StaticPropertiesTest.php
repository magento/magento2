<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Cleanup;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Verifies the TestFramework path filter used when backing up static properties.
 */
class StaticPropertiesTest extends TestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        if (class_exists(StaticProperties::class, false)) {
            return;
        }
        $root = getenv('CLONE_ROOT') ?: (defined('BP') ? BP : dirname(__DIR__, 8));
        require_once $root
            . '/dev/tests/integration/framework/Magento/TestFramework/Workaround/Cleanup/StaticProperties.php';
    }

    /**
     * @param string $classFile
     * @param bool $expected
     * @return void
     */
    #[DataProvider('isTestFrameworkFileDataProvider')]
    public function testIsTestFrameworkFile(string $classFile, bool $expected): void
    {
        $method = new ReflectionMethod(StaticProperties::class, 'isTestFrameworkFile');

        $this->assertSame($expected, $method->invoke(null, $classFile));
    }

    /**
     * @return array
     */
    public static function isTestFrameworkFileDataProvider(): array
    {
        return [
            'framework library file' => [
                '/var/www/html/lib/internal/Magento/Framework/Profiler.php',
                false,
            ],
            'test framework workaround file' => [
                '/var/www/html/dev/tests/integration/framework/Magento/TestFramework/Helper/Bootstrap.php',
                true,
            ],
        ];
    }
}
