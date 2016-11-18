<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

class FunctionDetectorTest extends \PHPUnit_Framework_TestCase
{
    public function testDetectFunctions()
    {
        $fixturePath = __DIR__ . '/_files/test.txt';
        $expectedResults = [
            24 => ['strtoupper', 'md5'],
            36 => ['security'],
            37 => ['security'],
        ];
        $functionDetector = new FunctionDetector();
        $lines = $functionDetector->detectFunctions($fixturePath, ['security', 'md5', 'test', 'strtoupper']);
        $this->assertEquals($expectedResults, $lines);
    }
}
