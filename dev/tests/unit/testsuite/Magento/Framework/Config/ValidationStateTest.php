<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

class ValidationStateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $appMode
     * @param boolean $expectedResult
     * @dataProvider isValidatedDataProvider
     */
    public function testIsValidated($appMode, $expectedResult)
    {
        $model = new \Magento\Framework\App\Arguments\ValidationState($appMode);
        $this->assertEquals($model->isValidated(), $expectedResult);
    }

    /**
     * @return array
     */
    public function isValidatedDataProvider()
    {
        return [
            [\Magento\Framework\App\State::MODE_DEVELOPER, true],
            [\Magento\Framework\App\State::MODE_DEFAULT, false],
            [\Magento\Framework\App\State::MODE_PRODUCTION, false]
        ];
    }
}
