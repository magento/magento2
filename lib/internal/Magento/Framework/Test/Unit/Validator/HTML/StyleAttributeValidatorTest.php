<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Validator\HTML;

use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\StyleAttributeValidator;
use PHPUnit\Framework\TestCase;

class StyleAttributeValidatorTest extends TestCase
{
    /**
     * Cases for "validate" test.
     *
     * @return array
     */
    public static function getAttributes(): array
    {
        return [
            'not a style' => ['class', 'value', true],
            'valid style' => ['style', 'color: blue', true],
            'invalid position style' => ['style', 'color: blue; position: absolute; width: 100%', false],
            'another invalid position style' => ['style', 'position: fixed; width: 100%', false],
            'valid position style' => ['style', 'color: blue; position: inherit; width: 100%', true],
            'valid background style' => ['style', 'color: blue; background-position: left; width: 100%', true],
            'invalid opacity style' => ['style', 'color: blue; width: 100%; opacity: 0.5', false],
            'invalid z-index style' => ['style', 'color: blue; width: 100%; z-index: 11', false]
        ];
    }

    /**
     * Test "validate" method.
     *
     * @param string $attr
     * @param string $value
     * @param bool $expectedValid
     * @return void
     * @dataProvider getAttributes
     */
    public function testValidate(string $attr, string $value, bool $expectedValid): void
    {
        $validator = new StyleAttributeValidator();

        try {
            $validator->validate('does not matter', $attr, $value);
            $actuallyValid = true;
        } catch (ValidationException $exception) {
            $actuallyValid = false;
        }
        $this->assertEquals($expectedValid, $actuallyValid);
    }
}
