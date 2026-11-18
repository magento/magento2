<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Code\Test\Unit\Generator\_files\SensitiveParameterSample;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use PHPUnit\Framework\TestCase;

/**
 * Verify sensitive parameter attributes in generated interceptors.
 */
class SensitiveParameterTest extends TestCase
{
    /**
     * Preserve attributes across constructor and method parameter forms.
     */
    public function testGenerate(): void
    {
        $io = $this->createMock(Io::class);
        $generator = $this->getMockBuilder(Interceptor::class)
            ->onlyMethods(['_validateData'])
            ->setConstructorArgs([
                SensitiveParameterSample::class,
                SensitiveParameterSample::class . '\\Interceptor',
                $io
            ])
            ->getMock();
        $generator->method('_validateData')->willReturn(true);
        $io->method('generateResultFileName')->willReturn('sensitive_interceptor.php');
        $io->expects($this->once())->method('writeResultFile')->with(
            'sensitive_interceptor.php',
            $this->callback(function (string $code): bool {
                $this->assertStringContainsString(
                    'function __construct(string $label, #[\\SensitiveParameter] string $password)',
                    $code
                );
                $this->assertStringContainsString(
                    'function authenticate(#[\\SensitiveParameter] string $password, string $label)',
                    $code
                );
                $this->assertStringContainsString(
                    'function nullable(string $label, #[\\SensitiveParameter] ?string $password = null)',
                    $code
                );
                $this->assertStringContainsString(
                    'function byReference(string $label, #[\\SensitiveParameter] string &$password)',
                    $code
                );
                $this->assertStringContainsString(
                    'function variadic(string $label, #[\\SensitiveParameter] string ... $passwords)',
                    $code
                );
                $this->assertSame(5, substr_count($code, '#[\\SensitiveParameter]'));
                return true;
            })
        );

        $this->assertSame('sensitive_interceptor.php', $generator->generate());
    }
}
