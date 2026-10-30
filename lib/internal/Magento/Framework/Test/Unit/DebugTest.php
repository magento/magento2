<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\Debug;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test code locations and trace formatting.
 */
class DebugTest extends TestCase
{
    /**
     * @var string|null
     */
    private $originalRootPath;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $rootPath = new \ReflectionProperty(Debug::class, '_filePath');
        $this->originalRootPath = $rootPath->getValue();
        $rootPath->setValue(null, '/var/www/magento');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $rootPath = new \ReflectionProperty(Debug::class, '_filePath');
        $rootPath->setValue(null, $this->originalRootPath);
    }

    /**
     * @param array|null $data
     * @param string $expected
     * @param string $root
     * @return void
     */
    #[DataProvider('codeLocationDataProvider')]
    public function testNormalizeCodeLocation(?array $data, string $expected, string $root = '/var/www/magento'): void
    {
        $rootPath = new \ReflectionProperty(Debug::class, '_filePath');
        $rootPath->setValue(null, $root);

        $this->assertSame($expected, Debug::normalizeCodeLocation($data));
    }

    /**
     * @return array
     */
    public static function codeLocationDataProvider(): array
    {
        return [
            'null frame' => [null, ''],
            'empty frame' => [[], ''],
            'missing file' => [['line' => 12], ''],
            'null file' => [['file' => null, 'line' => 12], ''],
            'file below root' => [
                ['file' => '/var/www/magento/app/code/Test.php', 'line' => 12],
                'app/code/Test.php:12'
            ],
            'missing line' => [['file' => '/var/www/magento/index.php'], 'index.php:0'],
            'null line' => [['file' => '/var/www/magento/index.php', 'line' => null], 'index.php:0'],
            'external file' => [['file' => '/opt/vendor/Test.php', 'line' => 9], '/opt/vendor/Test.php:9'],
            'sibling sharing root prefix' => [
                ['file' => '/var/www/magento-backup/Test.php', 'line' => 9],
                '/var/www/magento-backup/Test.php:9'
            ],
            'root embedded in external path' => [
                ['file' => '/backup/var/www/magento/Test.php', 'line' => 9],
                '/backup/var/www/magento/Test.php:9'
            ],
            'relative file' => [['file' => 'app/code/Test.php', 'line' => 12], 'app/code/Test.php:12'],
            'root path itself' => [['file' => '/var/www/magento', 'line' => 1], '/var/www/magento:1'],
            'root with trailing separator' => [
                ['file' => '/var/www/magento/app/code/Test.php', 'line' => 12],
                'app/code/Test.php:12',
                '/var/www/magento/'
            ],
            'filesystem root' => [['file' => '/app/code/Test.php', 'line' => 12], 'app/code/Test.php:12', '/']
        ];
    }

    /**
     * @param bool $html
     * @param bool $withArgs
     * @return void
     */
    #[DataProvider('traceFormatDataProvider')]
    public function testTrace(bool $html, bool $withArgs): void
    {
        $trace = [
            ['function' => 'skipped', 'file' => '/var/www/magento/skipped.php', 'line' => 1],
            [
                'class' => 'Vendor\\Module',
                'type' => '::',
                'function' => 'run',
                'args' => ["customer's", 42],
                'file' => '/var/www/magento/app/code/Test.php',
                'line' => 12
            ],
            ['function' => 'external', 'file' => '/backup/var/www/magento/Test.php', 'line' => 9],
            ['function' => 'sibling', 'file' => '/var/www/magento-backup/Test.php', 'line' => 7],
            ['function' => 'internal'],
            ['file' => '/var/www/magento/index.php'],
            []
        ];
        $args = $withArgs ? "'customer\\'s', 42" : '';
        $expected = "#1 Vendor\\Module::run($args) called at [app/code/Test.php:12]\n"
            . "#2 external() called at [/backup/var/www/magento/Test.php:9]\n"
            . "#3 sibling() called at [/var/www/magento-backup/Test.php:7]\n"
            . "#4 internal()\n"
            . "#5  called at [index.php:0]\n"
            . "#6 \n";
        if ($html) {
            $expected = '<pre>' . $expected . '</pre>';
        }

        $this->assertSame($expected, Debug::trace($trace, true, $html, $withArgs));
        $this->expectOutputString($expected);
        $this->assertTrue(Debug::trace($trace, false, $html, $withArgs));
    }

    /**
     * @return array
     */
    public static function traceFormatDataProvider(): array
    {
        return [
            'plain text without arguments' => [false, false],
            'plain text with arguments' => [false, true],
            'HTML without arguments' => [true, false],
            'HTML with arguments' => [true, true]
        ];
    }
}
