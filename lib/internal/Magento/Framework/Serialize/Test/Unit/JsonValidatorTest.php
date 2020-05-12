<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Serialize\Test\Unit;

use Magento\Framework\Serialize\JsonValidator;
use PHPUnit\Framework\TestCase;

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
     * @param bool $expected
     * @dataProvider isValidDataProvider
     */
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
    public function isValidDataProvider()
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
