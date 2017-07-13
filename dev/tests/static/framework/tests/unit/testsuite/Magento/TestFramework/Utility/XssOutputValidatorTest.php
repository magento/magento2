<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

class XssOutputValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @param string $expectedResults
     * @dataProvider getLinesWithXssSensitiveOutputDataProvider
     */
    public function testGetLinesWithXssSensitiveOutput($file, $expectedResults)
    {
        $xssOutputValidator = new XssOutputValidator();
        $lines = $xssOutputValidator->getLinesWithXssSensitiveOutput($file);
        static::assertEquals($expectedResults, $lines);
    }

    /**
     * @return array
     */
    public function getLinesWithXssSensitiveOutputDataProvider()
    {
        $fixturePath = __DIR__ . '/_files/';
        return [
            'xss_safe' => [$fixturePath . 'xss_safe.phtml', ''],
            'xss_unsafe' => [$fixturePath . 'xss_unsafe.phtml', '9,10,11,12,13,14,15,16,18,22,23'],
        ];
    }
}
