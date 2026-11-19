<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Code\Test\Unit\Generator\_files\SensitiveParameterSample;
use Magento\Framework\ObjectManager\Code\Generator\Proxy;
use PHPUnit\Framework\TestCase;

/**
 * Verify sensitive parameter attributes in generated proxies.
 */
class SensitiveParameterTest extends TestCase
{
    /**
     * Preserve sensitive method parameters without marking ordinary parameters.
     */
    public function testGenerate(): void
    {
        $io = $this->createMock(Io::class);
        $generator = $this->getMockBuilder(Proxy::class)
            ->onlyMethods(['_validateData'])
            ->setConstructorArgs([
                SensitiveParameterSample::class,
                null,
                $io,
                null,
                null
            ])
            ->getMock();
        $generator->method('_validateData')->willReturn(true);
        $io->method('generateResultFileName')->willReturn('sensitive_proxy.php');
        $io->expects($this->once())->method('writeResultFile')->with(
            'sensitive_proxy.php',
            $this->callback(function (string $code): bool {
                $this->assertStringContainsString(
                    'function authenticate(#[\\SensitiveParameter] string $password, string $label)',
                    $code
                );
                $this->assertSame(4, substr_count($code, '#[\\SensitiveParameter]'));
                return true;
            })
        );

        $this->assertSame('sensitive_proxy.php', $generator->generate());
    }
}
