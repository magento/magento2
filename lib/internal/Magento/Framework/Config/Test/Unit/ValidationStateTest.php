<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

class ValidationStateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $appMode
     * @param boolean $expectedResult
     * @dataProvider isValidationRequiredDataProvider
     */
    public function testIsValidationRequired($appMode, $expectedResult)
    {
        $model = new \Magento\Framework\App\Arguments\ValidationState($appMode);
        $this->assertEquals($model->isValidationRequired(), $expectedResult);
    }

    /**
     * @return array
     */
    public function isValidationRequiredDataProvider()
    {
        return [
            [\Magento\Framework\App\State::MODE_DEVELOPER, true],
            [\Magento\Framework\App\State::MODE_DEFAULT, false],
            [\Magento\Framework\App\State::MODE_PRODUCTION, false]
        ];
    }
}
