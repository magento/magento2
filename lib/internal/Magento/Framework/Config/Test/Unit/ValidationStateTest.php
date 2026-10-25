<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\App\Arguments\ValidationState;
use Magento\Framework\App\State;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ValidationStateTest extends TestCase
{
    /**
     * @param string $appMode
     * @param boolean $expectedResult     */
    #[DataProvider('isValidationRequiredDataProvider')]
    public function testIsValidationRequired($appMode, $expectedResult)
    {
        $model = new ValidationState($appMode);
        $this->assertEquals($model->isValidationRequired(), $expectedResult);
    }

    /**
     * @return array
     */
    public static function isValidationRequiredDataProvider()
    {
        return [
            [State::MODE_DEVELOPER, true],
            [State::MODE_DEFAULT, false],
            [State::MODE_PRODUCTION, false]
        ];
    }
}
