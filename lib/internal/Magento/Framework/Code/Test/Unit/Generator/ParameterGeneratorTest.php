<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Test\Unit\Generator;

use Laminas\Code\Generator\ParameterGenerator as LaminasParameterGenerator;
use Magento\Framework\Code\Generator\ParameterGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verify that adding redaction preserves Laminas parameter rendering.
 */
class ParameterGeneratorTest extends TestCase
{
    /**
     * Render ordinary parameters identically and prefix sensitive parameters.
     */
    #[DataProvider('parameterProvider')]
    public function testGenerate(array $options): void
    {
        $original = new LaminasParameterGenerator();
        $parameter = new ParameterGenerator();
        foreach ($options as $setter => $value) {
            $original->$setter($value);
            $parameter->$setter($value);
        }

        $this->assertSame($original->generate(), $parameter->generate());
        $parameter->setSensitiveParameter(true);
        $this->assertSame('#[\\SensitiveParameter] ' . $original->generate(), $parameter->generate());
        $parameter->setSensitiveParameter(false);
        $this->assertSame($original->generate(), $parameter->generate());
    }

    /**
     * Supply parameter forms whose rendering must remain unchanged.
     *
     * @return array
     */
    public static function parameterProvider(): array
    {
        return [
            'untyped' => [['setName' => 'password']],
            'typed' => [['setName' => 'password', 'setType' => 'string']],
            'nullable default' => [[
                'setName' => 'password',
                'setType' => '?string',
                'setDefaultValue' => null
            ]],
            'string default' => [[
                'setName' => 'password',
                'setType' => 'string',
                'setDefaultValue' => 'default'
            ]],
            'reference' => [['setName' => 'password', 'setType' => 'string', 'setPassedByReference' => true]],
            'variadic reference' => [[
                'setName' => 'passwords',
                'setType' => 'string',
                'setPassedByReference' => true,
                'setVariadic' => true
            ]]
        ];
    }
}
