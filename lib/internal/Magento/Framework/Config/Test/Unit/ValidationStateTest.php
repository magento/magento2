<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\App\Arguments\ValidationState;
use Magento\Framework\App\State;
use PHPUnit\Framework\TestCase;

class ValidationStateTest extends TestCase
{
    /**
     * @param string $appMode
     * @param boolean $expectedResult
     * @dataProvider isValidationRequiredDataProvider
     */
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
