<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\NewRelicReporting\Model\ServiceShellUser;
use PHPUnit\Framework\Attributes\DataProvider;

use PHPUnit\Framework\TestCase;

/**
 * Test for ServiceShellUser model
 *
 * @covers \Magento\NewRelicReporting\Model\ServiceShellUser
 */
class ServiceShellUserTest extends TestCase
{
    /**
     * @var ServiceShellUser
     */
    private ServiceShellUser $serviceShellUser;

    /**
     * Set up test dependencies
     */
    protected function setUp(): void
    {
        $this->serviceShellUser = new ServiceShellUser();
    }

    /**
     * Test get method with various inputs and expected outputs
     */
    #[DataProvider('valuesProvider')]
    public function testGetReturnsExpectedValue($input, $expected): void
    {
        $this->assertEquals($expected, $this->serviceShellUser->get($input));
    }

    /**
     * Test default user constant
     */
    public function testDefaultUserConstant(): void
    {
        $this->assertEquals('cron', ServiceShellUser::DEFAULT_USER);
    }

    /**
     * Data provider with input → expected output mapping
     */
    public static function valuesProvider(): array
    {
        return [
            // Truthy values → should return input directly
            'string_user'   => ['user', 'user'],
            'string_admin'  => ['admin', 'admin'],
            'string_space'  => [' ', ' '],       // space is truthy
            'integer_one'   => [1, 1],
            'float_value'   => [1.5, 1.5],
            'array_value'   => [['user'], ['user']],
            'boolean_true'  => [true, true],

            // Falsy values → should return "echo $USER" (current implementation)
            'boolean_false' => [false, 'echo $USER'],
            'empty_string'  => ['', 'echo $USER'],
            'string_zero'   => ['0', 'echo $USER'],
            'integer_zero'  => [0, 'echo $USER'],
            'float_zero'    => [0.0, 'echo $USER'],
            'null_value'    => [null, 'echo $USER'],
            'empty_array'   => [[], 'echo $USER'],
        ];
    }
}
