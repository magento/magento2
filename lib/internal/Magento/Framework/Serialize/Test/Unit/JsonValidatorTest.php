<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Serialize\Test\Unit;

use Magento\Framework\Serialize\JsonValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class JsonValidatorTest extends TestCase
{
    /**
     * @var JsonValidator
     */
    private $jsonValidator;

    protected function setUp(): void
    {
        $this->jsonValidator = new JsonValidator();
    }

    /**
     * @param string $value
     * @param bool $expected     */
    #[DataProvider('isValidDataProvider')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->jsonValidator->isValid($value)
        );
    }

    /**
     * @return array
     */
    public static function isValidDataProvider()
    {
        return [
            ['""', true],
            ['"string"', true],
            ['null', true],
            ['false', true],
            ['{"a":"b","d":123}', true],
            ['123', true],
            ['10.56', true],
            [123, true],
            [10.56, true],
            ['{}', true],
            ['"', false],
            ['"string', false],
            [null, false],
            [false, false],
            ['{"a', false],
            ['{', false],
            ['', false]
        ];
    }
}
