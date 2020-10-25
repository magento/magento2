<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit\Topology;

use InvalidArgumentException;
use Magento\Framework\Amqp\Topology\ArgumentProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArgumentProcessorTest extends TestCase
{
    /**
     * @var ArgumentProcessor|MockObject
     */
    private $argumentProcessor;

    /**
     * @return void
     */
    public function testProcessArgumentsWhenAnyArgumentIsIncorrect(): void
    {
        $arguments = [
            'test' => new class {
            }
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->argumentProcessor->processArguments($arguments);
    }

    /**
     * @return void
     */
    public function testProcessArgumentsWhenAllArgumentAreCorrect(): void
    {
        $arguments = [
            'array_type' => ['some_key' => 'some_value'],
            'numeric_value' => '25',
            'integer_value' => 26,
            'boolean_value' => false,
            'string_value' => 'test'
        ];

        $expected = [
            'array_type' => ['A', ['some_key' => 'some_value']],
            'numeric_value' => ['I', 25],
            'integer_value' => ['I', 26],
            'boolean_value' => ['t', false],
            'string_value' => ['S', 'test']
        ];

        $this->assertSame($expected, $this->argumentProcessor->processArguments($arguments));
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->argumentProcessor = $this->getMockForTrait(ArgumentProcessor::class);
    }
}
