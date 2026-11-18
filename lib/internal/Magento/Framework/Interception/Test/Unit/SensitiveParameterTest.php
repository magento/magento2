<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit;

use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Framework\Interception\DefinitionInterface;
use Magento\Framework\Interception\PluginListInterface;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\SensitiveSubject;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\SensitiveSubject\Plugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use SensitiveParameterValue;

/**
 * Verify that interception argument carriers redact exception traces.
 */
class SensitiveParameterTest extends TestCase
{
    /**
     * Runtime class produced by the real interceptor generator.
     */
    private const INTERCEPTOR_CLASS = SensitiveSubject::class . '\\Interceptor';

    /**
     * Inspect real trace frames for exceptions from plugins and parent methods.
     */
    #[DataProvider('failureProvider')]
    public function testTraceRedactsArguments(string $failure, bool $callsParent): void
    {
        if (filter_var(ini_get('zend.exception_ignore_args'), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Trace redaction requires zend.exception_ignore_args to be Off.');
        }

        $plugin = new Plugin();
        $pluginList = $this->createMock(PluginListInterface::class);
        $pluginList->method('getNext')->willReturnMap([
            [SensitiveSubject::class, 'authenticate', null, [DefinitionInterface::LISTENER_AROUND => 'throwing']],
            [SensitiveSubject::class, 'authenticate', 'throwing', null]
        ]);
        $pluginList->method('getPlugin')->willReturnMap([[SensitiveSubject::class, 'throwing', $plugin]]);
        $subject = $this->createInterceptor($pluginList);
        $secret = 'interception-secret';

        try {
            $subject->authenticate($secret, $failure);
            $this->fail('The interception fixture must throw.');
        } catch (RuntimeException $exception) {
            $trace = $exception->getTrace();
        }

        $this->assertPlumbingFrames($trace, [$secret, $failure], $callsParent);
        $methods = $this->findFrames($trace, 'authenticate');
        $this->assertCount($callsParent ? 2 : 1, $methods);
        foreach ($methods as $frame) {
            $this->assertRedacted($frame['args'][0], $secret);
            $this->assertSame($failure, $frame['args'][1]);
        }
    }

    /**
     * Instantiate generated code without initializing the global object manager.
     */
    private function createInterceptor(PluginListInterface $pluginList): SensitiveSubject
    {
        if (!class_exists(self::INTERCEPTOR_CLASS, false)) {
            $this->generateInterceptor();
        }

        $reflection = new ReflectionClass(self::INTERCEPTOR_CLASS);
        $subject = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('pluginList')->setValue($subject, $pluginList);
        $reflection->getProperty('subjectType')->setValue($subject, SensitiveSubject::class);
        $this->assertInstanceOf(SensitiveSubject::class, $subject);
        return $subject;
    }

    /**
     * Load real generator output from a temporary file without leaving artifacts.
     */
    private function generateInterceptor(): void
    {
        $io = $this->createMock(Io::class);
        $generator = $this->getMockBuilder(Interceptor::class)
            ->onlyMethods(['_validateData'])
            ->setConstructorArgs([SensitiveSubject::class, self::INTERCEPTOR_CLASS, $io])
            ->getMock();
        $generator->method('_validateData')->willReturn(true);
        $io->method('generateResultFileName')->willReturn('sensitive_interceptor.php');
        $io->expects($this->once())->method('writeResultFile')->with(
            'sensitive_interceptor.php',
            $this->callback(function (string $code): bool {
                $stream = tmpfile();
                $this->assertIsResource($stream);
                try {
                    fwrite($stream, "<?php\n" . $code);
                    require stream_get_meta_data($stream)['uri'];
                } finally {
                    fclose($stream);
                }
                return true;
            })
        );

        $this->assertSame('sensitive_interceptor.php', $generator->generate());
    }

    /**
     * Verify each carrier frame while retaining visible method names.
     */
    private function assertPlumbingFrames(array $trace, array $arguments, bool $callsParent): void
    {
        $plugins = $this->findFrames($trace, '___callPlugins');
        $this->assertCount(1, $plugins);
        $this->assertSame('authenticate', $plugins[0]['args'][0]);
        $this->assertRedacted($plugins[0]['args'][1], $arguments);

        $closures = array_values(array_filter(
            $trace,
            static fn (array $frame): bool => ($frame['class'] ?? '') === self::INTERCEPTOR_CLASS
                && str_contains($frame['function'], '{closure')
        ));
        $this->assertCount($callsParent ? 2 : 1, $closures);
        foreach ($closures as $frame) {
            $this->assertRedacted($frame['args'][0], $arguments[0]);
            $this->assertRedacted($frame['args'][1], $arguments[1]);
        }

        $parents = $this->findFrames($trace, '___callParent');
        $this->assertCount($callsParent ? 1 : 0, $parents);
        foreach ($parents as $frame) {
            $this->assertSame('authenticate', $frame['args'][0]);
            $this->assertRedacted($frame['args'][1], $arguments);
        }
    }

    /**
     * Verify that PHP wrapped the argument rather than exposing its value.
     */
    private function assertRedacted(mixed $argument, mixed $expectedValue): void
    {
        $this->assertInstanceOf(SensitiveParameterValue::class, $argument);
        $this->assertSame($expectedValue, $argument->getValue());
    }

    /**
     * Locate trace frames by their function name.
     */
    private function findFrames(array $trace, string $function): array
    {
        return array_values(array_filter(
            $trace,
            static fn (array $frame): bool => $frame['function'] === $function
        ));
    }

    /**
     * Exercise both ways an intercepted call can throw.
     *
     * @return array
     */
    public static function failureProvider(): array
    {
        return ['plugin throws' => ['plugin', false], 'parent throws' => ['parent', true]];
    }
}
