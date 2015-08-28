<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
            'xss_unsafe' => [$fixturePath . 'xss_unsafe.phtml', '1,2,3,4,5,6,7,9,13,14'],
        ];
    }
}
